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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Backend\Form\Element;

use EliasHaeussler\Typo3FormConsent\Tests;

/**
 * ConsentDataElementCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentDataElementCest
{
    public function canSeeConsentInBackendListModule(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->fillAndSubmitForm();

        $I->loginAs('admin');
        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::ListModule->value);

        $I->seeElement(Tests\Acceptance\Support\Enums\Selectors::ConsentListCollapsible->value);
        $I->scrollToElementInModule(Tests\Acceptance\Support\Enums\Selectors::ConsentListItemTitle->value);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::ConsentListItemTitle->value);
        $I->waitForText('Submitted form data', 5);
    }
}
