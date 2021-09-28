<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\Typo3FormConsent\Controller\ConsentController;
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Form\Element\ConsentDataElement;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask;

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
            'class' => ConsentDataElement::class,
        ];
    }

    /**
     * Register additional PageTsConfig.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerPageTsConfig(): void
    {
        ExtensionManagementUtility::addPageTSConfig('
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
        ExtensionUtility::configurePlugin(
            self::NAME,
            'Consent',
            [
                ConsentController::class => 'approve, dismiss',
            ],
            [
                ConsentController::class => 'approve, dismiss',
            ]
        );

        Icon::registerForPluginIdentifier('Consent');
    }

    /**
     * Register garbage collection task for consent table.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerGarbageCollectionTask(): void
    {
        if (!ExtensionManagementUtility::isLoaded('scheduler')) {
            return;
        }

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][TableGarbageCollectionTask::class]['options']['tables'][Consent::TABLE_NAME] = [
            'expireField' => 'valid_until',
        ];
    }
}
