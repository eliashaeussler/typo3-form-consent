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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Registry;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Registry\ConsentManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ConsentManagerRegistryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[CoversClass(ConsentManagerRegistry::class)]
final class ConsentManagerRegistryTest extends UnitTestCase
{
    protected Consent $consent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consent = new Consent();
        $this->consent->setFormPersistenceIdentifier('foo');
        $this->consent->setApproved();
    }

    #[Test]
    public function registerConsentGloballyRegistersConsent(): void
    {
        self::assertFalse(ConsentManagerRegistry::isConsentApproved('foo'));

        ConsentManagerRegistry::registerConsent($this->consent);

        self::assertTrue(ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Test]
    public function unregisterConsentGloballyUnregistersConsent(): void
    {
        ConsentManagerRegistry::registerConsent($this->consent);

        self::assertTrue(ConsentManagerRegistry::isConsentApproved('foo'));

        ConsentManagerRegistry::unregisterConsent($this->consent);

        self::assertFalse(ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Test]
    public function isConsentApprovedReturnsFalseIfConsentIsNotRegistered(): void
    {
        self::assertFalse(ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Test]
    public function isConsentApprovedReturnsStateOfApprovalOfRegisteredConsent(): void
    {
        ConsentManagerRegistry::registerConsent($this->consent);

        self::assertTrue(ConsentManagerRegistry::isConsentApproved('foo'));

        $this->consent->setDismissed();

        self::assertFalse(ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Test]
    public function isConsentDismissedReturnsFalseIfConsentIsNotRegistered(): void
    {
        self::assertFalse(ConsentManagerRegistry::isConsentDismissed('foo'));
    }

    #[Test]
    public function isConsentDismissedReturnsStateOfDismissalOfRegisteredConsent(): void
    {
        ConsentManagerRegistry::registerConsent($this->consent);

        $this->consent->setDismissed();

        self::assertTrue(ConsentManagerRegistry::isConsentDismissed('foo'));

        $this->consent->setApproved();

        self::assertFalse(ConsentManagerRegistry::isConsentDismissed('foo'));
    }

    protected function tearDown(): void
    {
        ConsentManagerRegistry::unregisterConsent($this->consent);

        parent::tearDown();
    }
}
