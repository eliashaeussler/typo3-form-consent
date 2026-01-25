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

use Composer\Autoload;
use Derhansen\FormCrshield;
use ShipMonk\ComposerDependencyAnalyser;
use TYPO3\CMS\Core;
use TYPO3\CMS\Form;

$rootPath = dirname(__DIR__, 2);

/** @var Autoload\ClassLoader $loader */
$loader = require $rootPath . '/.Build/vendor/autoload.php';
$loader->register();

$configuration = new ComposerDependencyAnalyser\Config\Configuration();
$configuration
    ->addPathsToExclude([
        $rootPath . '/Tests/Acceptance/Support/_generated',
        $rootPath . '/Tests/Build',
        $rootPath . '/Tests/CGL',
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
    ->ignoreUnknownClasses([
        Core\Crypto\HashAlgo::class,
        Form\Event\AfterCurrentPageIsResolvedEvent::class,
        Form\Event\BeforeRenderableIsValidatedEvent::class,
        FormCrshield\EventListener\FormCrShield::class,
    ])
;

return $configuration;
