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

namespace EliasHaeussler\Typo3FormConsent\DependencyInjection;

use EliasHaeussler\Typo3FormConsent\Widget;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

/**
 * DashboardServicesConfigurator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class DashboardServicesConfigurator
{
    private const APPROVED_CONSENTS_WIDGET = 'dashboard.widget.approved_consents_widget';
    private const APPROVED_CONSENTS_DATA_PROVIDER = 'form_consent.widget.approved_consents.data_provider';
    private const CONSENT_CONNECTION = 'connection.tx_formconsent_domain_model_consent';

    private Core\Information\Typo3Version $typo3Version;

    public function __construct(
        private DependencyInjection\Loader\Configurator\ServicesConfigurator $services,
    ) {
        $this->typo3Version = new Core\Information\Typo3Version();
    }

    public function configureServices(): void
    {
        $this->configureWidgets();
    }

    private function configureWidgets(): void
    {
        // Widget "approved consents"
        $service = $this->services->set(self::APPROVED_CONSENTS_WIDGET)
            ->autowire()
            ->class(Widget\ApprovedConsentsWidget::class)
            ->arg('$dataProvider', new DependencyInjection\Reference(self::APPROVED_CONSENTS_DATA_PROVIDER))
            ->tag('dashboard.widget', [
                'identifier' => 'approvedConsentsWidget',
                'groupNames' => 'general',
                'title' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:widgets.approvedConsents.header',
                'description' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:widgets.approvedConsents.body',
                'iconIdentifier' => 'content-widget-approved-consents',
                'height' => 'medium',
                'width' => 'small',
            ]);

        // @todo Remove once support for TYPO3 v12 is dropped
        if ($this->typo3Version->getMajorVersion() < 13) {
            $service->arg('$view', new DependencyInjection\Reference('dashboard.views.widget'));
        }

        // Data provider for widget "approved consents"
        $this->services->set(self::APPROVED_CONSENTS_DATA_PROVIDER)
            ->autowire()
            ->class(Widget\Provider\ConsentChartDataProvider::class)
            ->arg('$connection', new DependencyInjection\Reference(self::CONSENT_CONNECTION));
    }
}
