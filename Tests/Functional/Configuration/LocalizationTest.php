<?php

/** @noinspection TranslationMissingInspection */

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

use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * LocalizationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class LocalizationTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function forTableReturnsLocalizationKeyForGivenTable(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:dummy';
        self::assertSame($expected, Localization::forTable('dummy'));
    }

    /**
     * @test
     */
    public function forFieldReturnsLocalizationKeyForGivenTableField(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:dummy.foo';
        self::assertSame($expected, Localization::forField('foo', 'dummy'));
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:dummy.foo.baz';
        self::assertSame($expected, Localization::forField('foo', 'dummy', 'baz'));
    }

    /**
     * @test
     */
    public function forTabReturnsLocalizationKeyForGivenTab(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tabs.foo';
        self::assertSame($expected, Localization::forTab('foo'));
        $expected = 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:foo';
        self::assertSame($expected, Localization::forTab('foo', true));
    }

    /**
     * @test
     */
    public function forPluginReturnsLocalizationKeyForGivenPlugin(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:plugins.fooBaz';
        self::assertSame($expected, Localization::forPlugin('FooBaz'));
    }

    /**
     * @test
     */
    public function forFinisherOptionReturnsLocalizationKeyForGivenFinisherOption(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_form.xlf:finishers.foo.label';
        self::assertSame($expected, Localization::forFinisherOption('foo'));
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_form.xlf:finishers.foo.fieldExplanationText';
        self::assertSame($expected, Localization::forFinisherOption('foo', 'fieldExplanationText'));
    }

    /**
     * @test
     */
    public function forFormValidationReturnsLocalizationKeyForGivenValidation(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_form.xlf:validation.foo';
        self::assertSame($expected, Localization::forFormValidation('foo'));
    }

    /**
     * @test
     */
    public function forBackendFormReturnsLocalizationKeyForGivenValidation(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:form.foo';
        self::assertSame($expected, Localization::forBackendForm('foo'));
    }

    /**
     * @test
     */
    public function forWidgetReturnsLocalizationKeyForGivenWidget(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:widgets.foo.title';
        self::assertSame($expected, Localization::forWidget('foo', 'title'));
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:widgets.foo.description';
        self::assertSame($expected, Localization::forWidget('foo', 'description'));
    }

    /**
     * @test
     */
    public function forChartReturnsLocalizationKeyForGivenChart(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:charts.foo';
        self::assertSame($expected, Localization::forChart('foo'));
    }

    /**
     * @test
     * @dataProvider forKeyReturnsLocalizationKeyDataProvider
     */
    public function forKeyReturnsLocalizationKey(string $key, ?string $type, string $expected): void
    {
        self::assertSame($expected, Localization::forKey($key, $type));
    }

    /**
     * @test
     */
    public function translateReturnsTranslationFromTsfeIfEnvironmentIsInFrontendMode(): void
    {
        $this->simulateFrontendEnvironment();

        $localizationKey = Localization::forKey('foo');
        $expected = 'baz';
        self::assertSame($expected, Localization::translate($localizationKey));
    }

    /**
     * @test
     */
    public function translateReturnsLocalizationKeyIfLanguageServiceIsNotAvailable(): void
    {
        $GLOBALS['LANG'] = null;
        $localizationKey = Localization::forKey('foo');
        self::assertSame($localizationKey, Localization::translate($localizationKey));
    }

    /**
     * @test
     */
    public function translateReturnsTranslationIfLanguageServiceIsAvailable(): void
    {
        $this->simulateFrontendEnvironment();

        $localizationKey = Localization::forKey('foo');
        $expected = 'baz';
        self::assertSame($expected, Localization::translate($localizationKey));
    }

    /**
     * @test
     */
    public function translateReturnsEmptyStringIfTranslationIsNotAvailable(): void
    {
        $this->simulateFrontendEnvironment(null);

        $localizationKey = Localization::forKey('foo');
        $expected = '';
        self::assertSame($expected, Localization::translate($localizationKey));
    }

    protected function simulateFrontendEnvironment(?string $expectedReturnValue = 'baz'): void
    {
        $typoScriptFrontendControllerProphecy = $this->prophesize(TypoScriptFrontendController::class);
        $typoScriptFrontendControllerProphecy->sL(Argument::type('string'))->willReturn($expectedReturnValue);
        $GLOBALS['TSFE'] = $typoScriptFrontendControllerProphecy->reveal();
    }

    /**
     * @return \Generator<string, array{string, string|null, string}>
     */
    public function forKeyReturnsLocalizationKeyDataProvider(): \Generator
    {
        yield 'default type' => [
            'foo',
            Localization::TYPE_DEFAULT,
            'LLL:EXT:form_consent/Resources/Private/Language/locallang.xlf:foo',
        ];
        yield 'default type (nullable)' => [
            'foo',
            null,
            'LLL:EXT:form_consent/Resources/Private/Language/locallang.xlf:foo',
        ];
        yield 'database type' => [
            'foo',
            Localization::TYPE_DATABASE,
            'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:foo',
        ];
        yield 'core type' => [
            'foo',
            Localization::TYPE_CORE_TABS,
            'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:foo',
        ];
    }
}
