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

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ConsentManagerRegistryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Registry\ConsentManagerRegistry::class)]
final class ConsentManagerRegistryTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Domain\Model\Consent $consent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consent = new Src\Domain\Model\Consent();
        $this->consent->setFormPersistenceIdentifier('foo');
        $this->consent->setApproved();
    }

    #[Framework\Attributes\Test]
    public function registerConsentGloballyRegistersConsent(): void
    {
        self::assertFalse(Src\Registry\ConsentManagerRegistry::isConsentApproved('foo'));

        Src\Registry\ConsentManagerRegistry::registerConsent($this->consent);

        self::assertTrue(Src\Registry\ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Framework\Attributes\Test]
    public function unregisterConsentGloballyUnregistersConsent(): void
    {
        Src\Registry\ConsentManagerRegistry::registerConsent($this->consent);

        self::assertTrue(Src\Registry\ConsentManagerRegistry::isConsentApproved('foo'));

        Src\Registry\ConsentManagerRegistry::unregisterConsent($this->consent);

        self::assertFalse(Src\Registry\ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Framework\Attributes\Test]
    public function isConsentApprovedReturnsFalseIfConsentIsNotRegistered(): void
    {
        self::assertFalse(Src\Registry\ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Framework\Attributes\Test]
    public function isConsentApprovedReturnsStateOfApprovalOfRegisteredConsent(): void
    {
        Src\Registry\ConsentManagerRegistry::registerConsent($this->consent);

        self::assertTrue(Src\Registry\ConsentManagerRegistry::isConsentApproved('foo'));

        $this->consent->setDismissed();

        self::assertFalse(Src\Registry\ConsentManagerRegistry::isConsentApproved('foo'));
    }

    #[Framework\Attributes\Test]
    public function isConsentDismissedReturnsFalseIfConsentIsNotRegistered(): void
    {
        self::assertFalse(Src\Registry\ConsentManagerRegistry::isConsentDismissed('foo'));
    }

    #[Framework\Attributes\Test]
    public function isConsentDismissedReturnsStateOfDismissalOfRegisteredConsent(): void
    {
        Src\Registry\ConsentManagerRegistry::registerConsent($this->consent);

        $this->consent->setDismissed();

        self::assertTrue(Src\Registry\ConsentManagerRegistry::isConsentDismissed('foo'));

        $this->consent->setApproved();

        self::assertFalse(Src\Registry\ConsentManagerRegistry::isConsentDismissed('foo'));
    }

    protected function tearDown(): void
    {
        Src\Registry\ConsentManagerRegistry::unregisterConsent($this->consent);

        parent::tearDown();
    }
}
