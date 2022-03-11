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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Event;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Event\GenerateHashEvent;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * GenerateHashEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class GenerateHashEventTest extends UnitTestCase
{
    protected GenerateHashEvent $subject;

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

        $this->subject = new GenerateHashEvent($this->components, new Consent());
    }

    /**
     * @test
     */
    public function getComponentsReturnsInitialComponents(): void
    {
        $expected = $this->components;
        self::assertSame($expected, $this->subject->getComponents());
    }

    /**
     * @test
     */
    public function setComponentsAppliesComponentsCorrectly(): void
    {
        $components = ['dummy'];
        $this->subject->setComponents($components);
        self::assertSame($components, $this->subject->getComponents());
    }

    /**
     * @test
     */
    public function getConsentReturnsConsent(): void
    {
        $expected = new Consent();
        self::assertEquals($expected, $this->subject->getConsent());
    }

    /**
     * @test
     */
    public function getHashReturnsNullOnInitialState(): void
    {
        self::assertNull($this->subject->getHash());
    }

    /**
     * @test
     */
    public function setHashAppliesHashCorrectly(): void
    {
        $hash = 'foo';
        $this->subject->setHash($hash);
        self::assertSame($hash, $this->subject->getHash());
    }
}
