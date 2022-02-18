<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
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
class GenerateHashEventTest extends UnitTestCase
{
    /**
     * @var GenerateHashEvent
     */
    protected $subject;

    /**
     * @var mixed[]
     */
    protected $components = [
        'foo' => 'baz',
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
