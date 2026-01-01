<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Updates;

use TYPO3\CMS\Install;

/**
 * MigratePluginToContentElementUpgradeWizard
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Install\Attribute\UpgradeWizard('formConsentMigratePluginToContentElement')]
final class MigratePluginToContentElementUpgradeWizard extends Install\Updates\AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate EXT:form_consent plugin to content element';
    }

    public function getDescription(): string
    {
        return 'Migrates the EXT:form_consent plugin "Consent" from list_type "formconsent_consent" to CType "formconsent_consent".';
    }

    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'formconsent_consent' => 'formconsent_consent',
        ];
    }
}
