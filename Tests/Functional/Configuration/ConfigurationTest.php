<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Configuration;

use EliasHaeussler\Typo3FormConsent\Configuration\Configuration;
use EliasHaeussler\Typo3FormConsent\Configuration\Extension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * ConfigurationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[CoversClass(Configuration::class)]
final class ConfigurationTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected ExtensionConfiguration $extensionConfiguration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    }

    #[Test]
    public function getExcludedElementsFromPersistenceReturnsEmptyArrayIfConfigurationOptionDoesNotExist(): void
    {
        $this->extensionConfiguration->set(Extension::KEY, []);

        self::assertSame([], Configuration::getExcludedElementsFromPersistence());
    }

    #[Test]
    public function getExcludedElementsFromPersistenceReturnsEmptyArrayIfExtensionConfigurationIsMissing(): void
    {
        $this->extensionConfiguration->set(Extension::KEY);

        self::assertSame([], Configuration::getExcludedElementsFromPersistence());
    }

    #[Test]
    public function getExcludedElementsFromPersistenceReturnsExcludedElementsFromPersistence(): void
    {
        $this->extensionConfiguration->set(Extension::KEY, [
            'persistence' => [
                'excludedElements' => 'Honeypot, StaticText, , ContentElement',
            ],
        ]);

        $expected = [
            'Honeypot',
            'StaticText',
            'ContentElement',
        ];

        self::assertSame($expected, Configuration::getExcludedElementsFromPersistence());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $reflectionClass = new \ReflectionClass(Configuration::class);
        $reflectionProperty = $reflectionClass->getProperty('configuration');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }
}
