<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Helper;

use Codeception\Module;
use Facebook\WebDriver;
use TYPO3\CMS\Core;

/**
 * Form
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Form extends Module
{
    public const DEFAULT = 'contact-1';
    public const CONFIRMATION_AFTER_APPROVE = 'contact-confirmation-approve-variant-2';
    public const EMAIL_AFTER_APPROVE = 'contact-email-approve-variant-3';
    public const REDIRECT_AFTER_APPROVE = 'contact-redirect-approve-variant-4';
    public const V2 = 'contact-v2-5';
    public const INVALID = 'contact-invalid-6';
    public const CONFIRMATION_AFTER_DISMISS = 'contact-confirmation-dismiss-variant-7';
    public const REDIRECT_AFTER_DISMISS = 'contact-redirect-dismiss-variant-8';
    public const INVALID_CLOSURE_AFTER_APPROVE = 'contact-invalid-closure-approve-variant-9';
    public const INVALID_CLOSURE_AFTER_DISMISS = 'contact-invalid-closure-dismiss-variant-10';
    public const REDIRECT_AFTER_APPROVE_AND_DISMISS = 'contact-redirect-approve-dismiss-variant-11';
    public const VERIFY = 'contact-verify-12';

    public function fillAndSubmitForm(string $form = self::DEFAULT, bool $attachFile = false): void
    {
        $I = $this->getWebDriver();

        $elements = [
            $this->getFormElementName($form, 'email-1') => 'user@example.com',
        ];

        $this->waitForCrChallenge($form);

        if ($attachFile) {
            $I->attachFile(
                WebDriver\WebDriverBy::name($this->getFormElementName($form, 'fileupload-1')),
                'dummy.png',
            );
        }

        $I->submitForm(
            WebDriver\WebDriverBy::id($this->getFormElementIdentifier($form)),
            $elements,
            WebDriver\WebDriverBy::name($this->getFormElementName($form, '__currentPage'))
        );
    }

    private function waitForCrChallenge(string $form): void
    {
        $I = $this->getWebDriver();

        $crField = $this->getFormElementName($form, 'cr-field');
        $initialValue = $I->grabValueFrom($crField);

        $I->waitForElementChange(
            WebDriver\WebDriverBy::name($crField),
            static fn(WebDriver\WebDriverElement $element) => $element->getAttribute('value') !== $initialValue,
            10,
        );
    }

    private function getFormElementIdentifier(string $form, ?string $element = null): string
    {
        return $form . ($element !== null ? '-' . $element : '');
    }

    private function getFormElementName(string $form, ?string $element = null): string
    {
        $nameParts = [
            $form => [],
        ];

        if ($element !== null) {
            $nameParts[$form][$element] = null;
        }

        return substr(Core\Utility\GeneralUtility::implodeArrayForUrl('tx_form_formframework', $nameParts), 1, -1);
    }

    private function getWebDriver(): Module\WebDriver
    {
        if (!$this->hasModule('WebDriver')) {
            $this->fail('WebDriver module is not enabled.');
        }

        /** @var Module\WebDriver $webDriver */
        $webDriver = $this->getModule('WebDriver');

        return $webDriver;
    }
}
