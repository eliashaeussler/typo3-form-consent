<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Event;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * GenerateHashEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Event\GenerateHashEvent::class)]
final class GenerateHashEventTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Event\GenerateHashEvent $subject;

    /**
     * @var list<string>
     */
    protected array $components = [
        'foo',
        'baz',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Event\GenerateHashEvent($this->components, new Src\Domain\Model\Consent());
    }

    #[Framework\Attributes\Test]
    public function getComponentsReturnsInitialComponents(): void
    {
        $expected = $this->components;
        self::assertSame($expected, $this->subject->getComponents());
    }

    #[Framework\Attributes\Test]
    public function setComponentsAppliesComponentsCorrectly(): void
    {
        $components = ['dummy'];
        $this->subject->setComponents($components);
        self::assertSame($components, $this->subject->getComponents());
    }

    #[Framework\Attributes\Test]
    public function getConsentReturnsConsent(): void
    {
        $expected = new Src\Domain\Model\Consent();
        self::assertEquals($expected, $this->subject->getConsent());
    }

    #[Framework\Attributes\Test]
    public function getHashReturnsNullOnInitialState(): void
    {
        self::assertNull($this->subject->getHash());
    }

    #[Framework\Attributes\Test]
    public function setHashAppliesHashCorrectly(): void
    {
        $hash = 'foo';
        $this->subject->setHash($hash);
        self::assertSame($hash, $this->subject->getHash());
    }
}
