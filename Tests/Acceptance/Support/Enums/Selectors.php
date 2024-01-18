<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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
    case DashboardModalItemTitle = '.dashboard-modal-item-title';
    case DashboardModule = '[data-modulemenu-identifier="dashboard"]';
    case DashboardWidget = '.widget-identifier-approvedConsentsWidget';
    case DashboardWidgetCanvas = '.widget-identifier-approvedConsentsWidget canvas';
    case FormDefinition = '#t3-form-form-definition-label';
    case FormList = '#forms';
    case FormModule = '[data-modulemenu-identifier="web_FormFormbuilder"]';
    case FormPreviewMode = '[title="Preview mode"]';
    case ListModule = '[data-modulemenu-identifier="web_list"]';

    // @todo Can be removed once support for TYPO3 v11 is dropped
    case DashboardModuleV11 = '#dashboard';
    case FormModuleV11 = '#web_FormFormbuilder';
    case ListModuleV11 = '#web_list';
}
