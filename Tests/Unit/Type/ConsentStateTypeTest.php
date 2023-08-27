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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Type;

use EliasHaeussler\Typo3FormConsent as Src;
use Generator;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ConsentStateTypeTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Type\ConsentStateType::class)]
final class ConsentStateTypeTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Type\ConsentStateType $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Type\ConsentStateType();
    }

    #[Framework\Attributes\Test]
    public function constructorAcceptsNumericStringsForConsentState(): void
    {
        $actual = new Src\Type\ConsentStateType('1');

        self::assertSame('1', (string)$actual);
        self::assertTrue($actual->isApproved());
    }

    #[Framework\Attributes\Test]
    public function createNewReturnsNewObject(): void
    {
        $actual = Src\Type\ConsentStateType::createNew();

        self::assertTrue($actual->isNew());
        self::assertFalse($actual->isApproved());
        self::assertFalse($actual->isDismissed());
    }

    #[Framework\Attributes\Test]
    public function createApprovedReturnsApprovedObject(): void
    {
        $actual = Src\Type\ConsentStateType::createApproved();

        self::assertFalse($actual->isNew());
        self::assertTrue($actual->isApproved());
        self::assertFalse($actual->isDismissed());
    }

    #[Framework\Attributes\Test]
    public function createDismissedReturnsApprovedObject(): void
    {
        $actual = Src\Type\ConsentStateType::createDismissed();

        self::assertFalse($actual->isNew());
        self::assertFalse($actual->isApproved());
        self::assertTrue($actual->isDismissed());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('isNewReturnsConsentCreationStateDataProvider')]
    public function isNewReturnsConsentCreationState(Src\Enums\ConsentState $state, bool $expected): void
    {
        $subject = new Src\Type\ConsentStateType($state);

        self::assertSame($expected, $subject->isNew());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('isApprovedReturnsConsentApprovalStateDataProvider')]
    public function isApprovedReturnsConsentApprovalState(Src\Enums\ConsentState $state, bool $expected): void
    {
        $subject = new Src\Type\ConsentStateType($state);

        self::assertSame($expected, $subject->isApproved());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('isDismissedReturnsConsentDismissalStateDataProvider')]
    public function isDismissedReturnsConsentDismissalState(Src\Enums\ConsentState $state, bool $expected): void
    {
        $subject = new Src\Type\ConsentStateType($state);

        self::assertSame($expected, $subject->isDismissed());
    }

    #[Framework\Attributes\Test]
    public function objectIsStringable(): void
    {
        self::assertSame('0', (string)$this->subject);
    }

    /**
     * @return Generator<string, array{Src\Enums\ConsentState, bool}>
     */
    public static function isNewReturnsConsentCreationStateDataProvider(): Generator
    {
        yield 'new consent' => [Src\Enums\ConsentState::New, true];
        yield 'consent approved' => [Src\Enums\ConsentState::Approved, false];
        yield 'consent dismissed' => [Src\Enums\ConsentState::Dismissed, false];
    }

    /**
     * @return Generator<string, array{Src\Enums\ConsentState, bool}>
     */
    public static function isApprovedReturnsConsentApprovalStateDataProvider(): Generator
    {
        yield 'new consent' => [Src\Enums\ConsentState::New, false];
        yield 'consent approved' => [Src\Enums\ConsentState::Approved, true];
        yield 'consent dismissed' => [Src\Enums\ConsentState::Dismissed, false];
    }

    /**
     * @return Generator<string, array{Src\Enums\ConsentState, bool}>
     */
    public static function isDismissedReturnsConsentDismissalStateDataProvider(): Generator
    {
        yield 'new consent' => [Src\Enums\ConsentState::New, false];
        yield 'consent approved' => [Src\Enums\ConsentState::Approved, false];
        yield 'consent dismissed' => [Src\Enums\ConsentState::Dismissed, true];
    }
}
