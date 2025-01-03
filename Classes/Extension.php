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

namespace EliasHaeussler\Typo3FormConsent;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Scheduler;

/**
 * Extension
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 */
final class Extension
{
    public const KEY = 'form_consent';
    public const NAME = 'FormConsent';
    public const PLUGIN = 'Consent';

    /**
     * Register additional FormEngine node.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerFormEngineNode(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1576527415] = [
            'nodeName' => 'consentData',
            'priority' => 40,
            'class' => Form\Element\ConsentDataElement::class,
        ];
    }

    /**
     * Register additional PageTsConfig.
     *
     * FOR USE IN ext_localconf.php ONLY.
     *
     * @todo Remove once support for TYPO3 v12 is dropped
     */
    public static function registerPageTsConfig(): void
    {
        if ((new Core\Information\Typo3Version())->getMajorVersion() >= 13) {
            return;
        }

        Core\Utility\ExtensionManagementUtility::addPageTSConfig('
            @import "EXT:' . self::KEY . '/Configuration/TSconfig/Page.tsconfig"
        ');
    }

    /**
     * Register validation plugin.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerPlugin(): void
    {
        // @todo Migrate to CType once support for TYPO3 v12 is dropped
        Extbase\Utility\ExtensionUtility::configurePlugin(
            self::NAME,
            self::PLUGIN,
            [
                Controller\ConsentController::class => 'approve, dismiss',
            ],
            [
                Controller\ConsentController::class => 'approve, dismiss',
            ],
        );
    }

    /**
     * Register garbage collection task for consent table.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerGarbageCollectionTask(): void
    {
        if (!Core\Utility\ExtensionManagementUtility::isLoaded('scheduler')) {
            return;
        }

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables'][Domain\Model\Consent::TABLE_NAME] = [
            'expireField' => 'valid_until',
        ];
    }

    /**
     * Register global TypoScript setup.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerTypoScript(): void
    {
        Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1576524005 = EXT:form_consent/Configuration/Yaml/FormSetup.yaml
                    }
                }
            }
        ');
    }
}
