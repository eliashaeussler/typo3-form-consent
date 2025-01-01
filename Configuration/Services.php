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

namespace EliasHaeussler\Typo3FormConsent;

use Symfony\Component\DependencyInjection as SymfonyDI;
use TYPO3\CMS\Dashboard;

return static function (
    SymfonyDI\Loader\Configurator\ContainerConfigurator $containerConfigurator,
    SymfonyDI\ContainerBuilder $container,
): void {
    if ($container->hasDefinition(Dashboard\Widgets\ListWidget::class)) {
        $servicesConfigurator = new DependencyInjection\DashboardServicesConfigurator($containerConfigurator->services());
        $servicesConfigurator->configureServices();
    }
};
