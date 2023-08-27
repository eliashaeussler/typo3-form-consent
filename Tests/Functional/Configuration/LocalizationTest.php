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

use EliasHaeussler\Typo3FormConsent as Src;
use Generator;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * LocalizationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Configuration\Localization::class)]
final class LocalizationTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    #[Framework\Attributes\Test]
    public function forTableReturnsLocalizationKeyForGivenTable(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:dummy';
        self::assertSame($expected, Src\Configuration\Localization::forTable('dummy'));
    }

    #[Framework\Attributes\Test]
    public function forFieldReturnsLocalizationKeyForGivenTableField(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:dummy.foo';
        self::assertSame($expected, Src\Configuration\Localization::forField('foo', 'dummy'));
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:dummy.foo.baz';
        self::assertSame($expected, Src\Configuration\Localization::forField('foo', 'dummy', 'baz'));
    }

    #[Framework\Attributes\Test]
    public function forTabReturnsLocalizationKeyForGivenTab(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tabs.foo';
        self::assertSame($expected, Src\Configuration\Localization::forTab('foo'));
        $expected = 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:foo';
        self::assertSame($expected, Src\Configuration\Localization::forTab('foo', true));
    }

    #[Framework\Attributes\Test]
    public function forPluginReturnsLocalizationKeyForGivenPlugin(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:plugins.fooBaz';
        self::assertSame($expected, Src\Configuration\Localization::forPlugin('FooBaz'));
    }

    #[Framework\Attributes\Test]
    public function forFinisherOptionReturnsLocalizationKeyForGivenFinisherOption(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_form.xlf:finishers.foo.label';
        self::assertSame($expected, Src\Configuration\Localization::forFinisherOption('foo'));
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_form.xlf:finishers.foo.fieldExplanationText';
        self::assertSame($expected, Src\Configuration\Localization::forFinisherOption('foo', 'fieldExplanationText'));
    }

    #[Framework\Attributes\Test]
    public function forFormValidationReturnsLocalizationKeyForGivenValidation(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_form.xlf:validation.foo';
        self::assertSame($expected, Src\Configuration\Localization::forFormValidation('foo'));
    }

    #[Framework\Attributes\Test]
    public function forBackendFormReturnsLocalizationKeyForGivenValidation(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:form.foo';
        self::assertSame($expected, Src\Configuration\Localization::forBackendForm('foo'));
    }

    #[Framework\Attributes\Test]
    public function forWidgetReturnsLocalizationKeyForGivenWidget(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:widgets.foo.title';
        self::assertSame($expected, Src\Configuration\Localization::forWidget('foo', 'title'));
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:widgets.foo.description';
        self::assertSame($expected, Src\Configuration\Localization::forWidget('foo', 'description'));
    }

    #[Framework\Attributes\Test]
    public function forChartReturnsLocalizationKeyForGivenChart(): void
    {
        $expected = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:charts.foo';
        self::assertSame($expected, Src\Configuration\Localization::forChart('foo'));
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('forKeyReturnsLocalizationKeyDataProvider')]
    public function forKeyReturnsLocalizationKey(string $key, ?string $type, string $expected): void
    {
        self::assertSame($expected, Src\Configuration\Localization::forKey($key, $type));
    }

    #[Framework\Attributes\Test]
    public function translateReturnsTranslationFromTsfeIfEnvironmentIsInFrontendModeAndRequestIsAvailable(): void
    {
        $this->simulateFrontendEnvironment();

        $serverRequest = new Core\Http\ServerRequest();
        $GLOBALS['TYPO3_REQUEST'] = $serverRequest->withAttribute(
            'applicationType',
            Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE,
        );

        $localizationKey = Src\Configuration\Localization::forKey('foo');
        $expected = 'baz';
        self::assertSame($expected, Src\Configuration\Localization::translate($localizationKey));

        unset($GLOBALS['TYPO3_REQUEST']);
    }

    #[Framework\Attributes\Test]
    public function translateReturnsTranslationFromTsfeIfEnvironmentIsInFrontendModeAndRequestIsNotAvailable(): void
    {
        $this->simulateFrontendEnvironment();

        $localizationKey = Src\Configuration\Localization::forKey('foo');
        $expected = 'baz';
        self::assertSame($expected, Src\Configuration\Localization::translate($localizationKey));
    }

    #[Framework\Attributes\Test]
    public function translateReturnsLocalizationKeyIfLanguageServiceIsNotAvailable(): void
    {
        $GLOBALS['LANG'] = null;
        $localizationKey = Src\Configuration\Localization::forKey('foo');
        self::assertSame($localizationKey, Src\Configuration\Localization::translate($localizationKey));
    }

    #[Framework\Attributes\Test]
    public function translateReturnsTranslationIfLanguageServiceIsAvailable(): void
    {
        $this->simulateFrontendEnvironment();

        $localizationKey = Src\Configuration\Localization::forKey('foo');
        $expected = 'baz';
        self::assertSame($expected, Src\Configuration\Localization::translate($localizationKey));
    }

    #[Framework\Attributes\Test]
    public function translateReturnsEmptyStringIfTranslationIsNotAvailable(): void
    {
        $this->simulateFrontendEnvironment('');

        $localizationKey = Src\Configuration\Localization::forKey('foo');
        $expected = '';
        self::assertSame($expected, Src\Configuration\Localization::translate($localizationKey));
    }

    private function simulateFrontendEnvironment(string $expectedReturnValue = 'baz'): void
    {
        $tsfeMock = $this->createMock(Frontend\Controller\TypoScriptFrontendController::class);
        $tsfeMock->method('sL')
            ->with(new Framework\Constraint\IsType('string'))
            ->willReturn($expectedReturnValue)
        ;
        $GLOBALS['TSFE'] = $tsfeMock;
    }

    /**
     * @return Generator<string, array{string, string|null, string}>
     */
    public static function forKeyReturnsLocalizationKeyDataProvider(): Generator
    {
        yield 'default type' => [
            'foo',
            Src\Configuration\Localization::TYPE_DEFAULT,
            'LLL:EXT:form_consent/Resources/Private/Language/locallang.xlf:foo',
        ];
        yield 'default type (nullable)' => [
            'foo',
            null,
            'LLL:EXT:form_consent/Resources/Private/Language/locallang.xlf:foo',
        ];
        yield 'database type' => [
            'foo',
            Src\Configuration\Localization::TYPE_DATABASE,
            'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:foo',
        ];
        yield 'core type' => [
            'foo',
            Src\Configuration\Localization::TYPE_CORE_TABS,
            'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:foo',
        ];
    }
}
