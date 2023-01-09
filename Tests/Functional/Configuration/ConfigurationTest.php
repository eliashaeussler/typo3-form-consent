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
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * ConfigurationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConfigurationTest extends FunctionalTestCase
{
    protected ExtensionConfiguration $extensionConfiguration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
    }

    /**
     * @test
     */
    public function getExcludedElementsFromPersistenceReturnsEmptyArrayIfConfigurationOptionDoesNotExist(): void
    {
        $this->setExtensionConfiguration([]);

        self::assertSame([], Configuration::getExcludedElementsFromPersistence());
    }

    /**
     * @test
     */
    public function getExcludedElementsFromPersistenceReturnsEmptyArrayIfExtensionConfigurationIsMissing(): void
    {
        $this->setExtensionConfiguration(null);

        self::assertSame([], Configuration::getExcludedElementsFromPersistence());
    }

    /**
     * @test
     */
    public function getExcludedElementsFromPersistenceReturnsExcludedElementsFromPersistence(): void
    {
        $this->setExtensionConfiguration([
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

    /**
     * @param array<string, mixed>|null $configuration
     */
    private function setExtensionConfiguration(?array $configuration): void
    {
        // @todo Remove condition once v10 support is dropped
        if ($this->getMajorTypo3Version() >= 11) {
            $this->extensionConfiguration->set(Extension::KEY, $configuration);
        } else {
            /* @phpstan-ignore-next-line */
            $this->extensionConfiguration->set(Extension::KEY, '', $configuration);
        }
    }

    private function getMajorTypo3Version(): int
    {
        return (new Typo3Version())->getMajorVersion();
    }
}
