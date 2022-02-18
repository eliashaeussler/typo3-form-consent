<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Configuration;

use EliasHaeussler\Typo3FormConsent\Configuration\Icon;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * IconTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class IconTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider invalidIdentifierDataProvider
     * @param string $tableName
     */
    public function forTableThrowsExceptionIfNoTableNameIsGiven(string $tableName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1580308459);

        Icon::forTable($tableName);
    }

    /**
     * @test
     * @dataProvider forTableReturnsCorrectFileNameDataProvider
     * @param string|null $type
     * @param string $expected
     */
    public function forTableReturnsCorrectFileName(?string $type, string $expected): void
    {
        if ($type !== null) {
            self::assertSame($expected, Icon::forTable('dummy', $type));
        } else {
            self::assertSame($expected, Icon::forTable('dummy'));
        }
    }

    /**
     * @test
     * @dataProvider forPluginReturnsCorrectFileNameDataProvider
     * @param string|null $type
     * @param string $expected
     */
    public function forPluginReturnsCorrectFileName(?string $type, string $expected): void
    {
        if ($type !== null) {
            self::assertSame($expected, Icon::forPlugin('dummy', $type));
        } else {
            self::assertSame($expected, Icon::forPlugin('dummy'));
        }
    }

    /**
     * @test
     * @dataProvider invalidIdentifierDataProvider
     * @param string $pluginName
     */
    public function forPluginIdentifierThrowsExceptionIfPluginIdentifierIsInvalid(string $pluginName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1587655457);

        Icon::forPluginIdentifier($pluginName);
    }

    /**
     * @test
     * @dataProvider forPluginIdentifierReturnsCorrectPluginIdentifierDataProvider
     * @param string $pluginName
     * @param string $expected
     */
    public function forPluginIdentifierReturnsCorrectPluginIdentifier(string $pluginName, string $expected): void
    {
        self::assertSame($expected, Icon::forPluginIdentifier($pluginName));
    }

    /**
     * @test
     * @dataProvider forWidgetReturnsCorrectFileNameDataProvider
     * @param string|null $type
     * @param string $expected
     */
    public function forWidgetReturnsCorrectFileName(?string $type, string $expected): void
    {
        if ($type !== null) {
            self::assertSame($expected, Icon::forWidget('dummy', $type));
        } else {
            self::assertSame($expected, Icon::forWidget('dummy'));
        }
    }

    /**
     * @test
     * @dataProvider invalidIdentifierDataProvider
     * @param string $widgetName
     */
    public function forWidgetIdentifierThrowsExceptionIfWidgetIdentifierIsInvalid(string $widgetName): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632850400);

        Icon::forWidgetIdentifier($widgetName);
    }

    /**
     * @test
     * @dataProvider forWidgetIdentifierReturnsCorrectWidgetIdentifierDataProvider
     * @param string $widgetName
     * @param string $expected
     */
    public function forWidgetIdentifierReturnsCorrectWidgetIdentifier(string $widgetName, string $expected): void
    {
        self::assertSame($expected, Icon::forWidgetIdentifier($widgetName));
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public function invalidIdentifierDataProvider(): \Generator
    {
        yield 'empty string' => [''];
        yield 'whitespaces' => ['    '];
        yield 'line break' => [PHP_EOL];
    }

    /**
     * @return \Generator<string, array{string|null, string}>
     */
    public function forTableReturnsCorrectFileNameDataProvider(): \Generator
    {
        yield 'no type' => [null, 'EXT:form_consent/Resources/Public/Icons/dummy.svg'];
        yield 'custom type' => ['jpg', 'EXT:form_consent/Resources/Public/Icons/dummy.jpg'];
        yield 'non-trimmed type' => ['   svg  ', 'EXT:form_consent/Resources/Public/Icons/dummy.svg'];
        yield 'case-sensitive type' => ['jpEG', 'EXT:form_consent/Resources/Public/Icons/dummy.jpEG'];
    }

    /**
     * @return \Generator<string, array{string|null, string}>
     */
    public function forPluginReturnsCorrectFileNameDataProvider(): \Generator
    {
        yield 'no type' => [null, 'EXT:form_consent/Resources/Public/Icons/plugin.dummy.svg'];
        yield 'custom type' => ['jpg', 'EXT:form_consent/Resources/Public/Icons/plugin.dummy.jpg'];
        yield 'non-trimmed type' => ['   svg  ', 'EXT:form_consent/Resources/Public/Icons/plugin.dummy.svg'];
        yield 'case-sensitive type' => ['jpEG', 'EXT:form_consent/Resources/Public/Icons/plugin.dummy.jpEG'];
    }

    /**
     * @return \Generator<string, array{string, string}>
     */
    public function forPluginIdentifierReturnsCorrectPluginIdentifierDataProvider(): \Generator
    {
        yield 'valid plugin name' => ['foo', 'content-plugin-foo'];
        yield 'plugin name with whitespaces' => [' foo   ', 'content-plugin-foo'];
        yield 'plugin name in upper camelcase' => ['FooBaz', 'content-plugin-foo-baz'];
    }

    /**
     * @return \Generator<string, array{string|null, string}>
     */
    public function forWidgetReturnsCorrectFileNameDataProvider(): \Generator
    {
        yield 'no type' => [null, 'EXT:form_consent/Resources/Public/Icons/widget.dummy.svg'];
        yield 'custom type' => ['jpg', 'EXT:form_consent/Resources/Public/Icons/widget.dummy.jpg'];
        yield 'non-trimmed type' => ['   svg  ', 'EXT:form_consent/Resources/Public/Icons/widget.dummy.svg'];
        yield 'case-sensitive type' => ['jpEG', 'EXT:form_consent/Resources/Public/Icons/widget.dummy.jpEG'];
    }

    /**
     * @return \Generator<string, array{string, string}>
     */
    public function forWidgetIdentifierReturnsCorrectWidgetIdentifierDataProvider(): \Generator
    {
        yield 'valid widget name' => ['foo', 'content-widget-foo'];
        yield 'widget name with whitespaces' => [' foo   ', 'content-widget-foo'];
        yield 'widget name in upper camelcase' => ['FooBaz', 'content-widget-foo-baz'];
    }
}
