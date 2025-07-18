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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Extension;

use Codeception\Events;
use Codeception\Extension;
use EliasHaeussler\Typo3FormConsent\Tests;
use TYPO3\CMS\Core;
use TYPO3\CMS\Install;

/**
 * EnvironmentExtension
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class EnvironmentExtension extends Extension
{
    /**
     * @var array<Events::*, string>
     */
    public static array $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_BEFORE => 'beforeTest',
        Events::TEST_AFTER => 'afterTest',
    ];

    public function beforeSuite(): void
    {
        // Bootstrap TYPO3 environment
        require_once dirname(__DIR__, 4) . '/.Build/vendor/typo3/testing-framework/Resources/Core/Build/UnitTestsBootstrap.php';

        // Fix folder structure
        $defaultFactory = Core\Utility\GeneralUtility::makeInstance(Install\FolderStructure\DefaultFactory::class);
        $defaultFactory->getStructure()->fix();
    }

    public function beforeTest(): void
    {
        /** @var Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = $this->getModule(Tests\Acceptance\Support\Helper\ExtensionConfiguration::class);
        $extensionConfiguration->writeConfiguration('form_crshield', 'crJavaScriptDelay', '1');
    }

    public function afterTest(): void
    {
        /** @var Tests\Acceptance\Support\Helper\ExtensionConfiguration $extensionConfiguration */
        $extensionConfiguration = $this->getModule(Tests\Acceptance\Support\Helper\ExtensionConfiguration::class);
        $extensionConfiguration->removeConfiguration('form_crshield', 'crJavaScriptDelay');
    }
}
