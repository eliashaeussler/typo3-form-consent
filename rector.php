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
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    Config::create($rectorConfig, PhpVersion::PHP_81)
        ->in(
            __DIR__ . '/Classes',
            __DIR__ . '/Configuration',
            __DIR__ . '/Tests',
        )
        ->not(
            __DIR__ . '/.Build/*',
            __DIR__ . '/.ddev/*',
            __DIR__ . '/.github/*',
            __DIR__ . '/config/*',
            __DIR__ . '/Tests/Acceptance/Support/_generated/*',
            __DIR__ . '/var/*',
        )
        ->withPHPUnit()
        ->withSymfony()
        ->withTYPO3(Version::createMajor(11))
        ->skip(AnnotationToAttributeRector::class, [
            __DIR__ . '/Classes/Extension.php',
            __DIR__ . '/Classes/Updates/MigrateConsentStateUpgradeWizard.php',
        ])
        ->skip(ClassPropertyAssignToConstructorPromotionRector::class, [
            // We cannot use CPP for properties that are declared in abstract classes
            __DIR__ . '/Tests/Acceptance/Support/Helper/ModalDialog.php',
        ])
        ->skip(FinalizeClassesWithoutChildrenRector::class, [
            // Domain models and repositories should stay extendable
            __DIR__ . '/Classes/Domain/Model/*',
            __DIR__ . '/Classes/Domain/Repository/*',
        ])
        ->skip(PrivatizeFinalClassPropertyRector::class, [
            // Test properties must not be private, otherwise TF cannot perform GC tasks
            __DIR__ . '/Tests/Functional/*',
            __DIR__ . '/Tests/Unit/*',
        ])
        ->apply()
    ;

    $rectorConfig->importNames(false, false);
};
