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

namespace EliasHaeussler\Typo3FormConsent\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Configuration
{
    /**
     * @var array<string, mixed>|null
     */
    private static ?array $configuration = null;

    /**
     * @return list<string>
     */
    public static function getExcludedElementsFromPersistence(): array
    {
        $configurationValue = self::get('persistence/excludedElements');

        if (!\is_string($configurationValue)) {
            return [];
        }

        return GeneralUtility::trimExplode(',', $configurationValue, true);
    }

    /**
     * @return mixed|null
     */
    private static function get(string $path)
    {
        if (self::$configuration === null) {
            try {
                self::$configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(Extension::KEY);
            } catch (Exception $e) {
                self::$configuration = [];
            }
        }

        try {
            return ArrayUtility::getValueByPath(self::$configuration, $path);
        } catch (MissingArrayPathException $exception) {
            return null;
        }
    }
}
