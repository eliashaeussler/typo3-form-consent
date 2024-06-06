<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Domain\Model;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ConsentTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Model\Consent::class)]
final class ConsentTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Domain\Model\Consent $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Src\Domain\Model\Consent();
    }

    #[Framework\Attributes\Test]
    public function setEmailStoresEmailCorrectly(): void
    {
        $this->subject->setEmail('foo@baz.com');
        $expected = 'foo@baz.com';
        self::assertSame($expected, $this->subject->getEmail());
    }

    #[Framework\Attributes\Test]
    public function setDateStoresCreationDateCorrectly(): void
    {
        $date = new \DateTime();
        $this->subject->setDate($date);
        self::assertSame($date, $this->subject->getDate());
    }

    #[Framework\Attributes\Test]
    public function setDataStoresUserDataCorrectly(): void
    {
        $data = Src\Type\JsonType::fromArray(['foo' => 'baz']);
        $this->subject->setData($data);
        self::assertSame($data, $this->subject->getData());
    }

    #[Framework\Attributes\Test]
    public function setFormPersistenceIdentifierStoresFormPersistenceIdentifierCorrectly(): void
    {
        $this->subject->setFormPersistenceIdentifier('foo');
        self::assertSame('foo', $this->subject->getFormPersistenceIdentifier());
    }

    #[Framework\Attributes\Test]
    public function getOriginalRequestParametersReturnsNullOnInitialObject(): void
    {
        self::assertNull($this->subject->getValidUntil());
    }

    #[Framework\Attributes\Test]
    public function setOriginalRequestParametersStoresOriginalRequestParametersCorrectly(): void
    {
        $originalRequestParameters = Src\Type\JsonType::fromArray(['foo' => 'baz']);

        $this->subject->setOriginalRequestParameters($originalRequestParameters);

        self::assertSame($originalRequestParameters, $this->subject->getOriginalRequestParameters());
    }

    #[Framework\Attributes\Test]
    public function getOriginalContentElementUidReturnsZeroOnInitialState(): void
    {
        self::assertSame(0, $this->subject->getOriginalContentElementUid());
    }

    #[Framework\Attributes\Test]
    public function setOriginalContentElementUidStoresOriginalContentElementUidCorrectly(): void
    {
        $this->subject->setOriginalContentElementUid(123);

        self::assertSame(123, $this->subject->getOriginalContentElementUid());
    }

    #[Framework\Attributes\Test]
    public function getStateReturnsNullOnInitialState(): void
    {
        self::assertNull($this->subject->getState());
    }

    #[Framework\Attributes\Test]
    public function isApprovedReturnsFalseOnInitialState(): void
    {
        self::assertFalse($this->subject->isApproved());
    }

    #[Framework\Attributes\Test]
    public function setApprovedStoresApprovalCorrectly(): void
    {
        $this->subject->setApproved();

        self::assertTrue($this->subject->isApproved());
    }

    #[Framework\Attributes\Test]
    public function isDismissedReturnsFalseOnInitialState(): void
    {
        self::assertFalse($this->subject->isDismissed());
    }

    #[Framework\Attributes\Test]
    public function setDismissedStoresDismissalCorrectly(): void
    {
        $this->subject->setDismissed();

        self::assertTrue($this->subject->isDismissed());
    }

    #[Framework\Attributes\Test]
    public function setStateStoresConsentStateCorrectly(): void
    {
        $state = Src\Type\ConsentStateType::createNew();

        $this->subject->setState($state);

        self::assertSame($state, $this->subject->getState());
    }

    #[Framework\Attributes\Test]
    public function getUpdateDateReturnsNullOnInitialState(): void
    {
        self::assertNull($this->subject->getUpdateDate());
    }

    #[Framework\Attributes\Test]
    public function setUpdateDateStoresUpdateDateCorrectly(): void
    {
        $date = new \DateTime();

        $this->subject->setUpdateDate($date);

        self::assertSame($date, $this->subject->getUpdateDate());
    }

    #[Framework\Attributes\Test]
    public function getValidUntilReturnsNullOnInitialObject(): void
    {
        self::assertNull($this->subject->getValidUntil());
    }

    #[Framework\Attributes\Test]
    public function setValidUntilStoresLastPossibleApprovalDateCorrectly(): void
    {
        $date = \DateTime::createFromFormat('U', (string)(time() + 86400));
        self::assertInstanceOf(\DateTime::class, $date);
        $this->subject->setValidUntil($date);
        self::assertSame($date, $this->subject->getValidUntil());
    }

    #[Framework\Attributes\Test]
    public function setValidationHashStoresValidationHashCorrectly(): void
    {
        $this->subject->setValidationHash('dummy');
        $expected = 'dummy';
        self::assertSame($expected, $this->subject->getValidationHash());
    }
}
