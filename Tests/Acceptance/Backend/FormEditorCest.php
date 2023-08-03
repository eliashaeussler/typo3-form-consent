<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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
use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * FormEditorCest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class FormEditorCest
{
    public function canOpenFormWithConsentFinisherInPreviewMode(AcceptanceTester $I): void
    {
        $typo3Version = new Typo3Version();

        if ($typo3Version->getMajorVersion() >= 12) {
            $moduleIdentifier = '[data-modulemenu-identifier="web_FormFormbuilder"]';
        } else {
            // @todo Remove once support for TYPO3 v11 is dropped
            $moduleIdentifier = '#web_FormFormbuilder';
        }

        $I->loginAs('admin');
        $I->openModule($moduleIdentifier);

        $I->waitForText('contact');
        $I->click('contact', '#forms');

        $I->waitForText('contact', 5, '#t3-form-form-definition-label');
        $I->seeElement('[title="Preview mode"]');
        $I->click('[title="Preview mode"]');
        $I->waitForElement('form#contact');
    }
}
