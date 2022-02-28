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

use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Icon
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Icon
{
    private static ?IconRegistry $iconRegistry = null;

    public static function forTable(string $tableName, string $type = 'svg'): string
    {
        return self::buildIconPath($tableName, $type);
    }

    public static function forPlugin(string $pluginName, string $type = 'svg'): string
    {
        return self::buildIconPath('plugin.' . lcfirst($pluginName), $type);
    }

    public static function forPluginIdentifier(string $pluginName): string
    {
        $pluginName = trim($pluginName);
        $pluginName = str_replace('_', '-', GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));

        if ('' === $pluginName) {
            throw new \InvalidArgumentException(
                'Plugin name must not be empty when generating icon plugin identifier.',
                1587655457
            );
        }

        return 'content-plugin-' . $pluginName;
    }

    public static function registerForPluginIdentifier(string $pluginName, string $type = 'svg'): void
    {
        self::register(
            self::forPlugin($pluginName, $type),
            self::forPluginIdentifier($pluginName)
        );
    }

    public static function forWidget(string $widgetName, string $type = 'svg'): string
    {
        return self::buildIconPath('widget.' . lcfirst($widgetName), $type);
    }

    public static function forWidgetIdentifier(string $widgetName): string
    {
        $widgetName = trim($widgetName);
        $widgetName = str_replace('_', '-', GeneralUtility::camelCaseToLowerCaseUnderscored($widgetName));

        if ('' === $widgetName) {
            throw new \InvalidArgumentException(
                'Widget name must not be empty when generating icon widget identifier.',
                1632850400
            );
        }

        return 'content-widget-' . $widgetName;
    }

    public static function registerForWidgetIdentifier(string $widgetName, string $type = 'svg'): void
    {
        self::register(
            self::forWidget($widgetName, $type),
            self::forWidgetIdentifier($widgetName)
        );
    }

    private static function buildIconPath(string $fileName, string $type = 'svg'): string
    {
        $fileName = trim($fileName);
        $type = trim($type) ?: 'svg';

        if ('' === $fileName) {
            throw new \InvalidArgumentException('No icon filename given.', 1580308459);
        }

        return 'EXT:' . Extension::KEY . '/Resources/Public/Icons/' . $fileName . '.' . $type;
    }

    private static function register(string $filename, string $identifier): void
    {
        if (self::$iconRegistry === null) {
            self::$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        }

        $iconProviderClass = self::$iconRegistry->detectIconProvider($filename);
        self::$iconRegistry->registerIcon($identifier, $iconProviderClass, ['source' => $filename]);
    }
}
