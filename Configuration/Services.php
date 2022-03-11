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

namespace EliasHaeussler\Typo3FormConsent;

use EliasHaeussler\Typo3FormConsent\DependencyInjection\DashboardServicesConfigurator;
use EliasHaeussler\Typo3FormConsent\Type\Transformer\TypeTransformerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $container): void {
    $container->registerForAutoconfiguration(TypeTransformerInterface::class)
        ->addTag('form_consent.type_transformer');

    if ($container->hasDefinition('dashboard.views.widget')) {
        $servicesConfigurator = new DashboardServicesConfigurator($containerConfigurator->services());
        $servicesConfigurator->configureServices();
    }
};
