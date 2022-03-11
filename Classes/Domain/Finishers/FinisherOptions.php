<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent\Configuration\Extension;
use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use EliasHaeussler\Typo3FormConsent\Exception\NotAllowedException;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;

/**
 * FinisherOptions
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @implements \ArrayAccess<string, string|int|bool|TemplatePaths>
 */
final class FinisherOptions implements \ArrayAccess
{
    private static ?PageRepository $pageRepository = null;

    /**
     * @var callable(string): mixed
     */
    private $optionFetcher;

    /**
     * @var array{subject?: string, templatePaths?: TemplatePaths, recipientAddress?: string, recipientName?: string, senderAddress?: string, senderName?: string, approvalPeriod?: int, confirmationPid?: int, storagePid?: int, showDismissLink?: bool}
     */
    private array $parsedOptions = [];

    /**
     * @param callable(string): mixed $optionFetcher
     */
    public function __construct(callable $optionFetcher)
    {
        $this->optionFetcher = $optionFetcher;
    }

    public function getSubject(): string
    {
        if (isset($this->parsedOptions['subject'])) {
            return $this->parsedOptions['subject'];
        }

        $subject = trim((string)($this->optionFetcher)('subject'));

        if (str_starts_with($subject, 'LLL:')) {
            $subject = Localization::translate($subject);
        }
        if ($subject === '') {
            $subject = Localization::forKey('consentMail.subject', null, true);
        }

        return $this->parsedOptions['subject'] = $subject;
    }

    public function getTemplatePaths(): TemplatePaths
    {
        if (isset($this->parsedOptions['templatePaths'])) {
            return $this->parsedOptions['templatePaths'];
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $typoScriptConfiguration = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            Extension::NAME
        );
        $typoScriptTemplateConfiguration = $typoScriptConfiguration['view'] ?? [];

        $defaultTemplateConfiguration = $GLOBALS['TYPO3_CONF_VARS']['MAIL'];
        $finisherTemplateConfiguration = [
            'templateRootPaths' => ($this->optionFetcher)('templateRootPaths') ?? [],
            'partialRootPaths' => ($this->optionFetcher)('partialRootPaths') ?? [],
            'layoutRootPaths' => ($this->optionFetcher)('layoutRootPaths') ?? [],
        ];

        $mergedTemplateConfiguration = array_replace_recursive(
            $defaultTemplateConfiguration,
            $typoScriptTemplateConfiguration,
            $finisherTemplateConfiguration
        );

        return $this->parsedOptions['templatePaths'] = GeneralUtility::makeInstance(
            TemplatePaths::class,
            $mergedTemplateConfiguration
        );
    }

    public function getRecipientAddress(): string
    {
        if (isset($this->parsedOptions['recipientAddress'])) {
            return $this->parsedOptions['recipientAddress'];
        }

        $recipientAddress = ($this->optionFetcher)('recipientAddress');

        if (!\is_string($recipientAddress)) {
            $this->throwException('recipientAddress.invalid', 1640186663);
        }
        if ('' === trim($recipientAddress)) {
            $this->throwException('recipientAddress.empty', 1576947638);
        }
        if (!GeneralUtility::validEmail($recipientAddress)) {
            $this->throwException('recipientAddress.invalid', 1576947682);
        }

        return $this->parsedOptions['recipientAddress'] = $recipientAddress;
    }

    public function getRecipientName(): string
    {
        return $this->parsedOptions['recipientName']
            ?? $this->parsedOptions['recipientName'] = (string)($this->optionFetcher)('recipientName');
    }

    public function getSenderAddress(): string
    {
        if (isset($this->parsedOptions['senderAddress'])) {
            return $this->parsedOptions['senderAddress'];
        }

        $senderAddress = ($this->optionFetcher)('senderAddress');

        if (!\is_string($senderAddress)) {
            $this->throwException('senderAddress.invalid', 1640186811);
        }
        if ('' !== trim($senderAddress) && !GeneralUtility::validEmail($senderAddress)) {
            $this->throwException('senderAddress.invalid', 1587842752);
        }

        return $this->parsedOptions['senderAddress'] = $senderAddress;
    }

    public function getSenderName(): string
    {
        return $this->parsedOptions['senderName']
            ?? $this->parsedOptions['senderName'] = (string)($this->optionFetcher)('senderName');
    }

    public function getApprovalPeriod(): int
    {
        if (isset($this->parsedOptions['approvalPeriod'])) {
            return $this->parsedOptions['approvalPeriod'];
        }

        $approvalPeriod = (int)($this->optionFetcher)('approvalPeriod');

        if ($approvalPeriod < 0) {
            $this->throwException('approvalPeriod.invalid', 1576948900);
        }

        return $this->parsedOptions['approvalPeriod'] = $approvalPeriod;
    }

    public function getConfirmationPid(): int
    {
        if (isset($this->parsedOptions['confirmationPid'])) {
            return $this->parsedOptions['confirmationPid'];
        }

        $confirmationPid = (int)($this->optionFetcher)('confirmationPid');

        if ($confirmationPid <= 0) {
            $this->throwException('confirmationPid.empty', 1576948961);
        }
        if (!\is_array($this->getPageRepository()->checkRecord('pages', $confirmationPid))) {
            $this->throwException('confirmationPid.invalid', 1576949163);
        }

        return $this->parsedOptions['confirmationPid'] = $confirmationPid;
    }

    public function getStoragePid(): int
    {
        if (isset($this->parsedOptions['storagePid'])) {
            return $this->parsedOptions['storagePid'];
        }

        $storagePid = (int)($this->optionFetcher)('storagePid');

        // Return if storage pid is not set since it is not a mandatory option
        if ($storagePid === 0) {
            return $this->parsedOptions['storagePid'] = $storagePid;
        }

        if ($storagePid < 0) {
            $this->throwException('storagePid.empty', 1576951495);
        }
        if (!\is_array($this->getPageRepository()->checkRecord('pages', $storagePid))) {
            $this->throwException('storagePid.invalid', 1576951499);
        }

        return $this->parsedOptions['storagePid'] = $storagePid;
    }

    public function getShowDismissLink(): bool
    {
        return $this->parsedOptions['showDismissLink']
            ?? $this->parsedOptions['showDismissLink'] = (bool)($this->optionFetcher)('showDismissLink');
    }

    public function offsetExists($offset): bool
    {
        if (!\is_string($offset)) {
            return false;
        }

        $getterMethodName = 'get' . ucfirst($offset);
        if (method_exists($this, $getterMethodName)) {
            return true;
        }

        return false;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!\is_string($offset)) {
            return null;
        }

        $getterMethodName = 'get' . ucfirst($offset);
        if (method_exists($this, $getterMethodName)) {
            return $this->{$getterMethodName}();
        }

        return null;
    }

    public function offsetSet($offset, $value): void
    {
        throw NotAllowedException::forMethod(__METHOD__);
    }

    public function offsetUnset($offset): void
    {
        throw NotAllowedException::forMethod(__METHOD__);
    }

    private function getPageRepository(): PageRepository
    {
        if (null === self::$pageRepository) {
            self::$pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        }

        return self::$pageRepository;
    }

    /**
     * @throws FinisherException
     * @return never-returns
     */
    private function throwException(string $message, int $code = 0): void
    {
        throw new FinisherException(Localization::forFormValidation($message, true), $code);
    }
}
