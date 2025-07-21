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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Configuration;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ConfigurationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Configuration\Configuration::class)]
final class ConfigurationTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected Core\Configuration\ExtensionConfiguration $extensionConfiguration;
    protected Src\Configuration\Configuration $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extensionConfiguration = $this->get(Core\Configuration\ExtensionConfiguration::class);
        $this->subject = new Src\Configuration\Configuration($this->extensionConfiguration);
    }

    #[Framework\Attributes\Test]
    public function getExcludedElementsFromPersistenceReturnsEmptyArrayIfConfigurationOptionDoesNotExist(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, []);

        self::assertSame([], $this->subject->getExcludedElementsFromPersistence());
    }

    #[Framework\Attributes\Test]
    public function getExcludedElementsFromPersistenceReturnsEmptyArrayIfExtensionConfigurationIsMissing(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY);

        self::assertSame([], $this->subject->getExcludedElementsFromPersistence());
    }

    #[Framework\Attributes\Test]
    public function getExcludedElementsFromPersistenceReturnsExcludedElementsFromPersistence(): void
    {
        $this->extensionConfiguration->set(Src\Extension::KEY, [
            'persistence' => [
                'excludedElements' => 'Honeypot, StaticText, , ContentElement',
            ],
        ]);

        $expected = [
            'Honeypot',
            'StaticText',
            'ContentElement',
        ];

        self::assertSame($expected, $this->subject->getExcludedElementsFromPersistence());
    }
}
