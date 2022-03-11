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

namespace EliasHaeussler\Typo3FormConsent\DependencyInjection;

use EliasHaeussler\Typo3FormConsent\Configuration\Icon;
use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Widget\ApprovedConsentsWidget;
use EliasHaeussler\Typo3FormConsent\Widget\Provider\ConsentChartDataProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * DashboardServicesConfigurator
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DashboardServicesConfigurator
{
    private const APPROVED_CONSENTS_WIDGET = 'dashboard.widget.approved_consents_widget';
    private const APPROVED_CONSENTS_DATA_PROVIDER = 'form_consent.widget.approved_consents.data_provider';
    private const CONSENT_CONNECTION = 'form_consent.connection.consent';

    private ServicesConfigurator $services;

    public function __construct(ServicesConfigurator $services)
    {
        $this->services = $services;
    }

    public function configureServices(): void
    {
        $this->configureWidgets();
        $this->configureAdditionalServices();
    }

    private function configureWidgets(): void
    {
        // Widget "approved consents"
        $this->services->set(self::APPROVED_CONSENTS_WIDGET)
            ->class(ApprovedConsentsWidget::class)
            ->arg('$view', new Reference('dashboard.views.widget'))
            ->arg('$dataProvider', new Reference(self::APPROVED_CONSENTS_DATA_PROVIDER))
            ->tag('dashboard.widget', [
                'identifier' => 'approvedConsentsWidget',
                'groupNames' => 'general',
                'title' => Localization::forWidget('approvedConsents', 'header'),
                'description' => Localization::forWidget('approvedConsents', 'body'),
                'iconIdentifier' => Icon::forWidgetIdentifier('approvedConsents'),
                'height' => 'medium',
                'width' => 'small',
            ]);

        // Data provider for widget "approved consents"
        $this->services->set(self::APPROVED_CONSENTS_DATA_PROVIDER)
            ->class(ConsentChartDataProvider::class)
            ->arg('$connection', new Reference(self::CONSENT_CONNECTION));
    }

    private function configureAdditionalServices(): void
    {
        $this->services->set(self::CONSENT_CONNECTION)
            ->class(Connection::class)
            ->factory([new Reference(ConnectionPool::class), 'getConnectionForTable'])
            ->arg('$tableName', Consent::TABLE_NAME);
    }
}
