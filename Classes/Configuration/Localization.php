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

use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Localization
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Localization
{
    public const TYPE_DEFAULT = 'default';
    public const TYPE_BACKEND = 'be';
    public const TYPE_DATABASE = 'db';
    public const TYPE_FORM = 'form';
    public const TYPE_CORE_TABS = 'core_tabs';

    public const FILES = [
        self::TYPE_DEFAULT => 'locallang',
        self::TYPE_BACKEND => 'locallang_be',
        self::TYPE_DATABASE => 'locallang_db',
        self::TYPE_FORM => 'locallang_form',
        self::TYPE_CORE_TABS => 'Form/locallang_tabs',
    ];

    public static function forTable(string $tableName, bool $translate = false): string
    {
        return self::forKey($tableName, self::TYPE_DATABASE, $translate);
    }

    public static function forField(string $fieldName, string $tableName, string $item = null, bool $translate = false): string
    {
        $localizationKey = $tableName . '.' . $fieldName;
        if (\is_string($item) && \strlen($item) > 0) {
            $localizationKey .= '.' . $item;
        }

        return self::forKey($localizationKey, self::TYPE_DATABASE, $translate);
    }

    public static function forTab(string $tabName, bool $fromCore = false, bool $translate = false): string
    {
        if ($fromCore) {
            $type = self::TYPE_CORE_TABS;
            $localizationKey = $tabName;
        } else {
            $type = self::TYPE_DATABASE;
            $localizationKey = 'tabs.' . $tabName;
        }

        return self::forKey($localizationKey, $type, $translate);
    }

    public static function forPlugin(string $pluginName, bool $translate = false): string
    {
        return self::forKey('plugins.' . lcfirst($pluginName), self::TYPE_DATABASE, $translate);
    }

    public static function forFinisherOption(string $option, string $item = 'label', bool $translate = false): string
    {
        return self::forKey('finishers.' . $option . '.' . $item, self::TYPE_FORM, $translate);
    }

    public static function forFormValidation(string $key, bool $translate = false): string
    {
        return self::forKey('validation.' . $key, self::TYPE_FORM, $translate);
    }

    public static function forBackendForm(string $key, bool $translate = false): string
    {
        return self::forKey('form.' . $key, self::TYPE_BACKEND, $translate);
    }

    public static function forWidget(string $widgetName, string $item, bool $translate = false): string
    {
        return self::forKey('widgets.' . $widgetName . '.' . $item, self::TYPE_BACKEND, $translate);
    }

    public static function forChart(string $chartName, bool $translate = false): string
    {
        return self::forKey('charts.' . $chartName, self::TYPE_BACKEND, $translate);
    }

    public static function forKey(string $key, ?string $type = self::TYPE_DEFAULT, bool $translate = false): string
    {
        $localizationString = self::buildLocalizationString($type ?? self::TYPE_DEFAULT, $key);

        return $translate ? self::translate($localizationString) : $localizationString;
    }

    public static function translate(string $localizationKey): string
    {
        if (self::isEnvironmentInFrontendMode()) {
            return (string)self::getTypoScriptFrontendController()->sL($localizationKey);
        }
        if (self::getLanguageService() instanceof LanguageService) {
            return (string)self::getLanguageService()->sL($localizationKey);
        }

        return $localizationKey;
    }

    private static function buildLocalizationString(
        string $type = self::TYPE_DEFAULT,
        string $localizationKey = null,
        string $language = null
    ): string {
        $fileName = self::FILES[$type] ?? self::FILES[self::TYPE_DEFAULT];
        $language = $language ? ($language . '.') : '';
        $localizationKey = $localizationKey ? (':' . $localizationKey) : '';
        $extensionKey = self::isCoreType($type) ? 'core' : Extension::KEY;

        /** @noinspection TranslationMissingInspection */
        return sprintf(
            'LLL:EXT:%s/Resources/Private/Language/%s%s.xlf%s',
            $extensionKey,
            $language,
            $fileName,
            $localizationKey
        );
    }

    private static function isCoreType(string $type): bool
    {
        return \in_array($type, [
            self::TYPE_CORE_TABS,
        ]);
    }

    private static function isEnvironmentInFrontendMode(): bool
    {
        if (isset($GLOBALS['TYPO3_REQUEST'])) {
            return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
        }

        return isset($GLOBALS['TSFE']) && $GLOBALS['TSFE'] instanceof TypoScriptFrontendController;
    }

    private static function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }

    private static function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
