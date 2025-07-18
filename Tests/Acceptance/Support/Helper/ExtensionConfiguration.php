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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Helper;

use Codeception\Module;
use TYPO3\CMS\Core;

/**
 * ExtensionConfiguration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ExtensionConfiguration extends Module
{
    private Core\Configuration\ConfigurationManager $configurationManager;

    public function _initialize(): void
    {
        $this->configurationManager = Core\Utility\GeneralUtility::makeInstance(Core\Configuration\ConfigurationManager::class);
    }

    /**
     * @param non-empty-string $extension
     * @param non-empty-string $path
     */
    public function writeConfiguration(string $extension, string $path, mixed $value): void
    {
        $this->configurationManager->setLocalConfigurationValueByPath('EXTENSIONS/' . $extension . '/' . $path, $value);
        $this->clearCache();
    }

    /**
     * @param non-empty-string $extension
     * @param non-empty-string $path
     */
    public function removeConfiguration(string $extension, string $path): void
    {
        $this->configurationManager->removeLocalConfigurationKeysByPath(['EXTENSIONS/' . $extension . '/' . $path]);
        $this->clearCache();
    }

    private function clearCache(): void
    {
        /** @var Module\Cli $I */
        $I = $this->getModule('Cli');
        $I->runShellCommand('typo3 cache:flush');
    }
}
