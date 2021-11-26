<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Domain\Repository\ConsentRepository;
use EliasHaeussler\Typo3FormConsent\Event\ModifyConsentEvent;
use EliasHaeussler\Typo3FormConsent\Event\ModifyConsentMailEvent;
use EliasHaeussler\Typo3FormConsent\Service\HashService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\Domain\Finishers\FlashMessageFinisher;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Exception;

/**
 * ConsentFinisher
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConsentFinisher extends AbstractFinisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ConsentRepository
     */
    protected $consentRepository;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var FlashMessageFinisher
     */
    protected $flashMessageFinisher;

    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var array<string, mixed>
     */
    protected $configuration = [];

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $recipientAddress = '';

    /**
     * @var string
     */
    protected $recipientName = '';

    /**
     * @var string
     */
    protected $senderAddress = '';

    /**
     * @var string
     */
    protected $senderName = '';

    /**
     * @var int
     */
    protected $approvalPeriod = 0;

    /**
     * @var int
     */
    protected $confirmationPid = 0;

    /**
     * @var int
     */
    protected $storagePid = 0;

    /**
     * @var bool
     */
    protected $showDismissLink = false;

    public function injectContext(Context $context): void
    {
        $this->context = $context;
    }

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function injectConsentRepository(ConsentRepository $consentRepository): void
    {
        $this->consentRepository = $consentRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
        $this->configuration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            Extension::NAME
        );
    }

    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function injectFlashMessageFinisher(FlashMessageFinisher $flashMessageFinisher): void
    {
        $this->flashMessageFinisher = $flashMessageFinisher;
    }

    public function injectHashService(HashService $hashService): void
    {
        $this->hashService = $hashService;
    }

    public function injectPageRepository(PageRepository $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @throws IllegalObjectTypeException
     */
    protected function executeInternal(): ?string
    {
        try {
            $this->executeConsent();
        } catch (FinisherException $exception) {
            $this->addFlashMessage($exception);
        }

        return null;
    }

    /**
     * @throws FinisherException
     * @throws IllegalObjectTypeException
     * @throws \Exception
     */
    protected function executeConsent(): void
    {
        // Parse finisher options
        $this->subject = $this->resolveSubject((string)$this->parseOption('subject'));
        $this->recipientAddress = (string)$this->parseOption('recipientAddress');
        $this->recipientName = (string)$this->parseOption('recipientName');
        $this->senderAddress = (string)$this->parseOption('senderAddress');
        $this->senderName = (string)$this->parseOption('senderName');
        $this->approvalPeriod = (int)$this->parseOption('approvalPeriod');
        $this->confirmationPid = (int)$this->parseOption('confirmationPid');
        $this->storagePid = (int)$this->parseOption('storagePid');
        $this->showDismissLink = (bool)$this->parseOption('showDismissLink');

        // Validate finisher options
        $this->validateRecipientAddress();
        $this->validateSenderAddress();
        $this->validateApprovalPeriod();
        $this->validateConfirmationPid();
        $this->validateStoragePid();

        // Define consent variables
        $data = $this->resolveFormData();
        $formPersistenceIdentifier = $this->finisherContext->getFormRuntime()->getFormDefinition()->getPersistenceIdentifier();
        $date = new \DateTime('@' . $this->context->getPropertyFromAspect('date', 'timestamp', time()));
        $validUntil = $this->calculateExpiryDate($date);

        // Build domain model
        $consent = GeneralUtility::makeInstance(Consent::class)
            ->setEmail($this->recipientAddress)
            ->setDate($date)
            ->setData($data)
            ->setFormPersistenceIdentifier($formPersistenceIdentifier)
            ->setValidUntil($validUntil);

        // Build validation hash
        $validationHash = $this->hashService->generate($consent);
        $consent->setValidationHash($validationHash);

        // Apply storage pid if set, otherwise stick to default pid from TypoScript settings
        if ($this->storagePid) {
            $consent->setPid($this->storagePid);
        }

        // Dispatch ModifyConsent event
        $this->eventDispatcher->dispatch(new ModifyConsentEvent($consent));

        // Re-generate validation hash if consent has changed in the meantime
        if (!$this->hashService->isValid($consent)) {
            $validationHash = $this->hashService->generate($consent);
            $consent->setValidationHash($validationHash);
        }

        // Persist consent
        $this->consentRepository->add($consent);
        $this->persistenceManager->persistAll();

        // Build mail
        $mail = $this->initializeMail()
            ->assign('consent', $consent)
            ->assign('showDismissLink', $this->showDismissLink)
            ->assign('confirmationPid', $this->confirmationPid);

        // Dispatch ModifyConsentMail event
        $this->eventDispatcher->dispatch(new ModifyConsentMailEvent($mail));

        // Send mail
        try {
            $mailer = GeneralUtility::makeInstance(Mailer::class);
            $mailer->send($mail);
        } catch (TransportExceptionInterface $e) {
            throw new FinisherException(
                Localization::forKey('consentMail.error', null, true),
                1577109483
            );
        }
    }

    protected function resolveSubject(string $subject): string
    {
        $subject = trim($subject);
        if (strpos($subject, 'LLL:') === 0) {
            $subject = Localization::translate($subject);
        }
        if ($subject === '') {
            $subject = Localization::forKey('consentMail.subject', null, true);
        }
        return $subject;
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveFormData(): array
    {
        // Get all form values
        $formData = $this->finisherContext->getFormValues();

        // Remove honeypot field
        $honeypotIdentifier = $this->getHoneypotIdentifier();
        unset($formData[$honeypotIdentifier]);

        foreach ($formData as $key => $value) {
            if (is_object($value)) {
                if ($value instanceof ExtbaseFileReference) {
                    $value = $value->getOriginalResource();
                }
                if ($value instanceof CoreFileReference) {
                    $formData[$key] = $value->getOriginalFile()->getUid();
                }
            }
        }

        return $formData;
    }

    protected function getHoneypotIdentifier(): ?string
    {
        // Get last displayed page
        $formRuntime = $this->finisherContext->getFormRuntime();
        $lastDisplayedPageIndex = $formRuntime->getFormState()->getLastDisplayedPageIndex();
        try {
            $currentPage = $formRuntime->getFormDefinition()->getPageByIndex($lastDisplayedPageIndex);
        } catch (Exception $e) {
            // If last displayed page is not set, try to use current page instead
            $currentPage = $formRuntime->getCurrentPage();
        }

        // Early return if neither last displayed page nor current page are available
        if ($currentPage === null) {
            return null;
        }

        // Build honeypot session identifier
        $frontendUser = $this->getTypoScriptFrontendController()->fe_user;
        $isUserAuthenticated = (bool)$this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        $sessionType = $isUserAuthenticated ? 'user' : 'ses';
        $honeypotSessionIdentifier = implode('', [
            FormRuntime::HONEYPOT_NAME_SESSION_IDENTIFIER,
            $formRuntime->getIdentifier(),
            $currentPage->getIdentifier(),
        ]);

        return (string)$frontendUser->getKey($sessionType, $honeypotSessionIdentifier) ?: null;
    }

    /**
     * @param \DateTime|null $base
     * @return \DateTime|null
     * @throws \Exception
     */
    protected function calculateExpiryDate(\DateTime $base = null): ?\DateTime
    {
        // Early return if invalid approval period is given
        if ($this->approvalPeriod <= 0) {
            return null;
        }

        $base = $base !== null ? clone $base : new \DateTime();
        $target = $base->getTimestamp() + $this->approvalPeriod;

        return new \DateTime('@' . $target);
    }

    protected function initializeMail(): FluidEmail
    {
        $defaultTemplateConfiguration = $GLOBALS['TYPO3_CONF_VARS']['MAIL'];
        $typoScriptTemplateConfiguration = $this->configuration['view'] ?? [];
        $finisherTemplateConfiguration = [
            'templateRootPaths' => $this->options['templateRootPaths'] ?? [],
            'partialRootPaths' => $this->options['partialRootPaths'] ?? [],
            'layoutRootPaths' => $this->options['layoutRootPaths'] ?? [],
        ];
        $mergedTemplateConfiguration = array_replace_recursive(
            $defaultTemplateConfiguration,
            $typoScriptTemplateConfiguration,
            $finisherTemplateConfiguration
        );
        $templatePaths = GeneralUtility::makeInstance(TemplatePaths::class, $mergedTemplateConfiguration);

        $mail = GeneralUtility::makeInstance(FluidEmail::class, $templatePaths)
            ->to(new Address($this->recipientAddress, $this->recipientName))
            ->subject($this->subject)
            ->setTemplate('ConsentMail');

        if ($this->senderAddress !== '') {
            $mail->from(new Address($this->senderAddress, $this->senderName));
        }

        // Set the PSR-7 request object if available
        $serverRequest = $this->getServerRequest();
        if (null !== $serverRequest) {
            $mail->setRequest($serverRequest);
        }

        return $mail;
    }

    /**
     * @throws FinisherException
     */
    protected function validateRecipientAddress(): void
    {
        if ('' === trim($this->recipientAddress)) {
            throw new FinisherException(
                Localization::forFormValidation('recipientAddress.empty', true),
                1576947638
            );
        }
        if (!GeneralUtility::validEmail($this->recipientAddress)) {
            throw new FinisherException(
                Localization::forFormValidation('recipientAddress.invalid', true),
                1576947682
            );
        }
    }

    /**
     * @throws FinisherException
     */
    protected function validateSenderAddress(): void
    {
        if ('' !== trim($this->senderAddress) && !GeneralUtility::validEmail($this->senderAddress)) {
            throw new FinisherException(
                Localization::forFormValidation('senderAddress.invalid', true),
                1587842752
            );
        }
    }

    /**
     * @throws FinisherException
     */
    protected function validateApprovalPeriod(): void
    {
        if ($this->approvalPeriod < 0) {
            throw new FinisherException(
                Localization::forFormValidation('validationPeriod.invalid', true),
                1576948900
            );
        }
    }

    /**
     * @throws FinisherException
     */
    protected function validateConfirmationPid(): void
    {
        if ($this->confirmationPid <= 0) {
            throw new FinisherException(
                Localization::forFormValidation('confirmationPid.empty', true),
                1576948961
            );
        }
        if (!is_array($this->pageRepository->checkRecord('pages', $this->confirmationPid))) {
            throw new FinisherException(
                Localization::forFormValidation('confirmationPid.invalid', true),
                1576949163
            );
        }
    }

    /**
     * @throws FinisherException
     */
    protected function validateStoragePid(): void
    {
        // Return if storage pid is not set since it is not a mandatory option
        if ($this->storagePid === 0) {
            return;
        }

        if ($this->storagePid < 0) {
            throw new FinisherException(
                Localization::forFormValidation('storagePid.empty', true),
                1576951495
            );
        }
        if (!is_array($this->pageRepository->checkRecord('pages', $this->storagePid))) {
            throw new FinisherException(
                Localization::forFormValidation('storagePid.invalid', true),
                1576951499
            );
        }
    }

    /**
     * @param \Exception $exception
     * @param bool $cancel
     */
    protected function addFlashMessage(\Exception $exception, bool $cancel = true): void
    {
        // Add flash message
        $this->flashMessageFinisher->setOptions([
            'messageBody' => $exception->getMessage(),
            'messageCode' => $exception->getCode(),
            'severity' => AbstractMessage::ERROR,
        ]);
        $this->flashMessageFinisher->execute($this->finisherContext);

        // Cancel execution
        if ($cancel) {
            $this->finisherContext->cancel();
        }
    }

    protected function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
