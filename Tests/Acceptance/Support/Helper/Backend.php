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

use EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\AcceptanceTester;

/**
 * Backend
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Backend
{
    private const USERNAME = 'admin';
    private const PASSWORD = 'password';

    private AcceptanceTester $tester;
    private ModalDialog $dialog;

    public function __construct(AcceptanceTester $tester, ModalDialog $dialog)
    {
        $this->tester = $tester;
        $this->dialog = $dialog;
    }

    public function login(): void
    {
        $I = $this->tester;

        $I->amOnPage('/typo3/');
        $I->waitForElementVisible('#t3-username');
        $I->waitForElementVisible('#t3-password');
        $I->fillField('#t3-username', self::USERNAME);
        $I->fillField('#t3-password', self::PASSWORD);
        $I->click('#t3-login-submit');
        $I->dontSeeElement('#typo3-login-form');

        try {
            $this->dialog->clickButtonInDialog('[name=ok]');
        } catch (\Exception $e) {
            // If dialog is not present, that's fine...
        }
    }

    /**
     * @throws \Exception
     */
    public function openModule(string $identifier): void
    {
        $I = $this->tester;

        $I->waitForElementClickable($identifier, 5);
        $I->click($identifier);
        $I->switchToIFrame('#typo3-contentIframe');
    }
}
