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
    case ConsentListCollapsible = '#t3-table-tx_formconsent_domain_model_consent';
    case ConsentListItemTitle = 'tr[data-table="tx_formconsent_domain_model_consent"]:first-child td.col-title a';
    case ContactForm = 'form#contact';
    case DashboardAddWidgetButton = '.js-dashboard-addWidget';
    case DashboardModalItemTitle = '[data-identifier="approvedConsentsWidget"]';
    case DashboardModule = '[data-modulemenu-identifier="dashboard"]';
    case DashboardWidget = '.widget-identifier-approvedConsentsWidget';
    case DashboardWidgetCanvas = '.widget-identifier-approvedConsentsWidget canvas';
    case FormDefinition = '[data-identifier="formDefinitionLabel"]';
    case FormList = '#forms';
    case FormModule = '[data-modulemenu-identifier="web_FormFormbuilder"]';
    case FormPreviewMode = '[title="Preview mode"]';
    case ListModule = '[data-modulemenu-identifier="web_list"]';

    // @todo Remove once support for TYPO3 v12 is dropped
    case DashboardModalItemTitleV12 = '.dashboard-modal-item-title';
}
