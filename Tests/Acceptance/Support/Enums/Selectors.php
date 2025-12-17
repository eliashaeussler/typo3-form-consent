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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Enums;

/**
 * Selectors
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum Selectors: string
{
    public const ConsentListCollapsible = '#t3-table-tx_formconsent_domain_model_consent';
    public const ConsentListItemTitle = 'tr[data-table="tx_formconsent_domain_model_consent"]:first-child td.col-title a';
    public const ContactForm = 'form#contact';
    public const DashboardAddWidgetButton = '.dashboard-add-item > button';
    public const DashboardModalItemTitle = '[data-identifier="approvedConsentsWidget"]';
    public const DashboardModule = '[data-modulemenu-identifier="dashboard"]';
    public const DashboardWidget = '[data-widget-key="approvedConsentsWidget"]';
    public const DashboardWidgetCanvas = '[data-widget-key="approvedConsentsWidget"] canvas';
    public const FormDefinition = '[data-identifier="formDefinitionLabel"]';
    public const FormList = '#forms';
    public const FormModule = '[data-modulemenu-identifier="web_FormFormbuilder"]';
    public const FormPreviewMode = '[title="Preview mode"]';
    public const RecordsModule = '[data-moduleroute-identifier="records"]';

    // @todo Remove once support for TYPO3 v13 is dropped
    public const RecordsModuleLegacy = '[data-moduleroute-identifier="web_list"]';
}
