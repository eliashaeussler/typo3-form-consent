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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Domain\Model;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Type\ConsentStateType;
use EliasHaeussler\Typo3FormConsent\Type\JsonType;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ConsentTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentTest extends UnitTestCase
{
    protected Consent $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Consent();
    }

    #[Test]
    public function setEmailStoresEmailCorrectly(): void
    {
        $this->subject->setEmail('foo@baz.com');
        $expected = 'foo@baz.com';
        self::assertSame($expected, $this->subject->getEmail());
    }

    #[Test]
    public function setDateStoresCreationDateCorrectly(): void
    {
        $date = new \DateTime();
        $this->subject->setDate($date);
        self::assertSame($date, $this->subject->getDate());
    }

    #[Test]
    public function setDataStoresUserDataCorrectly(): void
    {
        $data = JsonType::fromArray(['foo' => 'baz']);
        $this->subject->setData($data);
        self::assertSame($data, $this->subject->getData());
    }

    #[Test]
    public function setFormPersistenceIdentifierStoresFormPersistenceIdentifierCorrectly(): void
    {
        $this->subject->setFormPersistenceIdentifier('foo');
        self::assertSame('foo', $this->subject->getFormPersistenceIdentifier());
    }

    #[Test]
    public function getOriginalRequestParametersReturnsNullOnInitialObject(): void
    {
        self::assertNull($this->subject->getValidUntil());
    }

    #[Test]
    public function setOriginalRequestParametersStoresOriginalRequestParametersCorrectly(): void
    {
        $originalRequestParameters = JsonType::fromArray(['foo' => 'baz']);

        $this->subject->setOriginalRequestParameters($originalRequestParameters);

        self::assertSame($originalRequestParameters, $this->subject->getOriginalRequestParameters());
    }

    #[Test]
    public function getOriginalContentElementUidReturnsZeroOnInitialState(): void
    {
        self::assertSame(0, $this->subject->getOriginalContentElementUid());
    }

    #[Test]
    public function setOriginalContentElementUidStoresOriginalContentElementUidCorrectly(): void
    {
        $this->subject->setOriginalContentElementUid(123);

        self::assertSame(123, $this->subject->getOriginalContentElementUid());
    }

    #[Test]
    public function getStateReturnsNullOnInitialState(): void
    {
        self::assertNull($this->subject->getState());
    }

    #[Test]
    public function isApprovedReturnsFalseOnInitialState(): void
    {
        self::assertFalse($this->subject->isApproved());
    }

    #[Test]
    public function setApprovedStoresApprovalCorrectly(): void
    {
        $this->subject->setApproved();

        self::assertTrue($this->subject->isApproved());
    }

    #[Test]
    public function isDismissedReturnsFalseOnInitialState(): void
    {
        self::assertFalse($this->subject->isDismissed());
    }

    #[Test]
    public function setDismissedStoresDismissalCorrectly(): void
    {
        $this->subject->setDismissed();

        self::assertTrue($this->subject->isDismissed());
    }

    #[Test]
    public function setStateStoresConsentStateCorrectly(): void
    {
        $state = ConsentStateType::createNew();

        $this->subject->setState($state);

        self::assertSame($state, $this->subject->getState());
    }

    #[Test]
    public function getUpdateDateReturnsNullOnInitialState(): void
    {
        self::assertNull($this->subject->getUpdateDate());
    }

    #[Test]
    public function setUpdateDateStoresUpdateDateCorrectly(): void
    {
        $date = new \DateTime();

        $this->subject->setUpdateDate($date);

        self::assertSame($date, $this->subject->getUpdateDate());
    }

    #[Test]
    public function getValidUntilReturnsNullOnInitialObject(): void
    {
        self::assertNull($this->subject->getValidUntil());
    }

    #[Test]
    public function setValidUntilStoresLastPossibleApprovalDateCorrectly(): void
    {
        $date = \DateTime::createFromFormat('U', (string)(time() + 86400));
        self::assertInstanceOf(\DateTime::class, $date);
        $this->subject->setValidUntil($date);
        self::assertSame($date, $this->subject->getValidUntil());
    }

    #[Test]
    public function setValidationHashStoresValidationHashCorrectly(): void
    {
        $this->subject->setValidationHash('dummy');
        $expected = 'dummy';
        self::assertSame($expected, $this->subject->getValidationHash());
    }
}
