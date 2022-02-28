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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Configuration;

use EliasHaeussler\Typo3FormConsent\Configuration\Icon;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * IconTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class IconTest extends FunctionalTestCase
{
    protected IconRegistry $iconRegistry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iconRegistry = $this->getContainer()->get(IconRegistry::class);
    }

    /**
     * @test
     */
    public function registerForPluginIdentifierRegistersIconCorrectly(): void
    {
        Icon::registerForPluginIdentifier('Consent');

        $actual = $this->iconRegistry->getIconConfigurationByIdentifier('content-plugin-consent');
        $expected = [
            'provider' => SvgIconProvider::class,
            'options' => [
                'source' => 'EXT:form_consent/Resources/Public/Icons/plugin.consent.svg',
            ],
        ];

        self::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function registerForWidgetIdentifierRegistersIconCorrectly(): void
    {
        Icon::registerForWidgetIdentifier('approvedConsents');

        $actual = $this->iconRegistry->getIconConfigurationByIdentifier('content-widget-approved-consents');
        $expected = [
            'provider' => SvgIconProvider::class,
            'options' => [
                'source' => 'EXT:form_consent/Resources/Public/Icons/widget.approvedConsents.svg',
            ],
        ];

        self::assertSame($expected, $actual);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset icon registry in subject
        $reflectionClass = new \ReflectionClass(Icon::class);
        $reflectionProperty = $reflectionClass->getProperty('iconRegistry');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null);
    }
}
