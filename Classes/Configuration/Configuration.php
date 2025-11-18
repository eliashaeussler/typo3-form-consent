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

namespace EliasHaeussler\Typo3FormConsent\Configuration;

use EliasHaeussler\Typo3FormConsent\Extension;
use TYPO3\CMS\Core;

/**
 * Configuration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class Configuration
{
    public function __construct(
        private Core\Configuration\ExtensionConfiguration $configuration,
    ) {}

    /**
     * @return list<string>
     */
    public function getExcludedElementsFromPersistence(): array
    {
        try {
            $configurationValue = $this->configuration->get(Extension::KEY, 'persistence/excludedElements');

            if (!\is_string($configurationValue)) {
                return [];
            }

            return Core\Utility\GeneralUtility::trimExplode(',', $configurationValue, true);
        } catch (Core\Exception) {
            return [];
        }
    }
}
