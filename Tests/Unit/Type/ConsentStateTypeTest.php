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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Type;

use EliasHaeussler\Typo3FormConsent\Exception\InvalidStateException;
use EliasHaeussler\Typo3FormConsent\Type\ConsentStateType;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ConsentStateTypeTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentStateTypeTest extends UnitTestCase
{
    protected ConsentStateType $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ConsentStateType();
    }

    /**
     * @test
     * @dataProvider constructorThrowsExceptionOnTypeMismatchDataProvider
     * @param mixed $state
     */
    public function constructorThrowsExceptionOnTypeMismatch($state): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionCode(1648199565);

        new ConsentStateType($state);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionOnInvalidState(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->expectExceptionCode(1648199643);

        new ConsentStateType(99);
    }

    /**
     * @test
     */
    public function constructorAcceptsNumericStringsForConsentState(): void
    {
        $actual = new ConsentStateType('1');

        self::assertSame('1', (string)$actual);
        self::assertTrue($actual->isApproved());
    }

    /**
     * @test
     */
    public function createNewReturnsNewObject(): void
    {
        $actual = ConsentStateType::createNew();

        self::assertInstanceOf(ConsentStateType::class, $actual);
        self::assertTrue($actual->isNew());
        self::assertFalse($actual->isApproved());
        self::assertFalse($actual->isDismissed());
    }

    /**
     * @test
     */
    public function createApprovedReturnsApprovedObject(): void
    {
        $actual = ConsentStateType::createApproved();

        self::assertInstanceOf(ConsentStateType::class, $actual);
        self::assertFalse($actual->isNew());
        self::assertTrue($actual->isApproved());
        self::assertFalse($actual->isDismissed());
    }

    /**
     * @test
     */
    public function createDismissedReturnsApprovedObject(): void
    {
        $actual = ConsentStateType::createDismissed();

        self::assertInstanceOf(ConsentStateType::class, $actual);
        self::assertFalse($actual->isNew());
        self::assertFalse($actual->isApproved());
        self::assertTrue($actual->isDismissed());
    }

    /**
     * @test
     * @dataProvider isNewReturnsConsentCreationStateDataProvider
     */
    public function isNewReturnsConsentCreationState(int $state, bool $expected): void
    {
        $subject = new ConsentStateType($state);

        self::assertSame($expected, $subject->isNew());
    }

    /**
     * @test
     * @dataProvider isApprovedReturnsConsentApprovalStateDataProvider
     */
    public function isApprovedReturnsConsentApprovalState(int $state, bool $expected): void
    {
        $subject = new ConsentStateType($state);

        self::assertSame($expected, $subject->isApproved());
    }

    /**
     * @test
     * @dataProvider isDismissedReturnsConsentDismissalStateDataProvider
     */
    public function isDismissedReturnsConsentDismissalState(int $state, bool $expected): void
    {
        $subject = new ConsentStateType($state);

        self::assertSame($expected, $subject->isDismissed());
    }

    /**
     * @test
     */
    public function objectIsStringable(): void
    {
        self::assertSame('0', (string)$this->subject);
    }

    /**
     * @return \Generator<string, array{mixed}>
     */
    public function constructorThrowsExceptionOnTypeMismatchDataProvider(): \Generator
    {
        yield 'null' => [null];
        yield 'bool' => [false];
        yield 'object' => [new \stdClass()];
    }

    /**
     * @return \Generator<string, array{int, bool}>
     */
    public function isNewReturnsConsentCreationStateDataProvider(): \Generator
    {
        yield 'new consent' => [ConsentStateType::NEW, true];
        yield 'consent approved' => [ConsentStateType::APPROVED, false];
        yield 'consent dismissed' => [ConsentStateType::DISMISSED, false];
    }

    /**
     * @return \Generator<string, array{int, bool}>
     */
    public function isApprovedReturnsConsentApprovalStateDataProvider(): \Generator
    {
        yield 'new consent' => [ConsentStateType::NEW, false];
        yield 'consent approved' => [ConsentStateType::APPROVED, true];
        yield 'consent dismissed' => [ConsentStateType::DISMISSED, false];
    }

    /**
     * @return \Generator<string, array{int, bool}>
     */
    public function isDismissedReturnsConsentDismissalStateDataProvider(): \Generator
    {
        yield 'new consent' => [ConsentStateType::NEW, false];
        yield 'consent approved' => [ConsentStateType::APPROVED, false];
        yield 'consent dismissed' => [ConsentStateType::DISMISSED, true];
    }
}
