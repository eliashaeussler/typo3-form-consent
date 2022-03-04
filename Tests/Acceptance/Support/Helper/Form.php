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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Helper;

use Codeception\Module;
use Facebook\WebDriver\WebDriverBy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Form
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Form extends Module
{
    public const DEFAULT = 'contact-1';
    public const CONFIRMATION_AFTER_APPROVE = 'contact-confirmation-variant-2';
    public const EMAIL_AFTER_APPROVE = 'contact-email-variant-3';
    public const REDIRECT_AFTER_APPROVE = 'contact-redirect-variant-4';
    public const V2 = 'contact-v2-5';
    public const INVALID = 'contact-invalid-6';

    public function fillAndSubmitForm(string $form = self::DEFAULT, bool $attachFile = false): void
    {
        $I = $this->getWebDriver();

        $elements = [
            $this->getFormElementName($form, 'email-1') => 'user@example.com',
        ];

        if ($attachFile) {
            $I->attachFile(
                sprintf('[name="%s"]', $this->getFormElementName($form, 'fileupload-1')),
                'dummy.png'
            );
        }

        $I->submitForm(
            WebDriverBy::id($this->getFormElementIdentifier($form)),
            $elements,
            WebDriverBy::name($this->getFormElementName($form, '__currentPage'))
        );
    }

    private function getFormElementIdentifier(string $form, string $element = null): string
    {
        return $form . (null !== $element ? '-' . $element : '');
    }

    private function getFormElementName(string $form, string $element = null): string
    {
        $nameParts = [
            $form => [],
        ];

        if (null !== $element) {
            $nameParts[$form][$element] = null;
        }

        return substr(GeneralUtility::implodeArrayForUrl('tx_form_formframework', $nameParts), 1, -1);
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
