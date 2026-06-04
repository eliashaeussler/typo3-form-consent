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

use Derhansen\FormCrshield;
use ShipMonk\ComposerDependencyAnalyser;

$rootPath = dirname(__DIR__, 2);
$configuration = new ComposerDependencyAnalyser\Config\Configuration();
$configuration
    ->addPathsToExclude([
        $rootPath . '/Tests/Acceptance/Support/_generated',
    ])
    ->ignoreErrorsOnPackages(
        [
            'php-webdriver/webdriver',
            'phpunit/phpunit',
        ],
        [ComposerDependencyAnalyser\Config\ErrorType::SHADOW_DEPENDENCY],
    )
    ->ignoreErrorsOnPackages(
        [
            'derhansen/form_crshield',
            'typo3/cms-dashboard',
            'typo3/cms-scheduler',
        ],
        [
            ComposerDependencyAnalyser\Config\ErrorType::DEV_DEPENDENCY_IN_PROD,
        ],
    )
    ->ignoreErrorsOnPackages(
        [
            'typo3/cms-install',
        ],
        [
            ComposerDependencyAnalyser\Config\ErrorType::PROD_DEPENDENCY_ONLY_IN_DEV,
        ],
    )
    ->ignoreUnknownClasses([
        FormCrshield\Hooks\Form::class,
    ])
;

return $configuration;
