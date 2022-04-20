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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Backend;

use EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\AcceptanceTester;
use EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Helper\Backend;

/**
 * ConsentDataElementCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentDataElementCest
{
    public function canSeeConsentInBackendListModule(AcceptanceTester $I, Backend $backend): void
    {
        $I->amOnPage('/');
        $I->fillAndSubmitForm();

        $backend->login();
        $backend->openModule('#web_list');

        $I->seeElement('#t3-table-tx_formconsent_domain_model_consent');
        $I->click('tr[data-table="tx_formconsent_domain_model_consent"]:first-child td.col-title a');
        $I->waitForText('Submitted form data', 5);
    }
}
