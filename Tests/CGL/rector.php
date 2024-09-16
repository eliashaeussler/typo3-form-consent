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

use EliasHaeussler\RectorConfig\Config\Config;
use EliasHaeussler\RectorConfig\Entity\Version;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rootPath = dirname(__DIR__, 2);

    require $rootPath . '/.Build/vendor/autoload.php';

    Config::create($rectorConfig, PhpVersion::PHP_81)
        ->in(
            $rootPath . '/Classes',
            $rootPath . '/Configuration',
            $rootPath . '/Tests',
        )
        ->not(
            $rootPath . '/.Build/*',
            $rootPath . '/.ddev/*',
            $rootPath . '/.github/*',
            $rootPath . '/config/*',
            $rootPath . '/Tests/Acceptance/Support/_generated/*',
            $rootPath . '/Tests/Build/Configuration/system/settings.php',
            $rootPath . '/Tests/CGL/vendor/*',
            $rootPath . '/var/*',
        )
        ->withPHPUnit()
        ->withSymfony()
        ->withTYPO3(Version::createMajor(12))
        ->skip(AnnotationToAttributeRector::class, [
            $rootPath . '/Classes/Extension.php',
            $rootPath . '/Classes/Updates/MigrateConsentStateUpgradeWizard.php',
        ])
        ->skip(ClassPropertyAssignToConstructorPromotionRector::class, [
            // We cannot use CPP for properties that are declared in abstract classes
            $rootPath . '/Tests/Acceptance/Support/Helper/ModalDialog.php',
        ])
        ->skip(PrivatizeFinalClassMethodRector::class, [
            // previewAction in ConsentController must be visible to base ActionController
            $rootPath . '/Classes/Controller/ConsentController.php',
        ])
        ->skip(PrivatizeFinalClassPropertyRector::class, [
            // Test properties must not be private, otherwise TF cannot perform GC tasks
            $rootPath . '/Tests/Functional/*',
            $rootPath . '/Tests/Unit/*',
        ])
        ->apply()
    ;

    $rectorConfig->importNames(false, false);
};
