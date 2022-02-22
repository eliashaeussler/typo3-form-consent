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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Service;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Event\GenerateHashEvent;
use EliasHaeussler\Typo3FormConsent\Service\HashService;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * HashServiceTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class HashServiceTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var Consent
     */
    protected $consent;

    /**
     * @var ObjectProphecy|EventDispatcherInterface
     */
    protected $eventDispatcherProphecy;

    /**
     * @var HashService
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consent = (new Consent())
            ->setEmail('dummy@example.com')
            ->setDate(new \DateTime())
            ->setData(['foo' => 'baz'])
            ->setValidUntil(new \DateTime());

        $this->eventDispatcherProphecy = $this->prophesize(EventDispatcherInterface::class);
        $this->eventDispatcherProphecy->dispatch(Argument::any())->willReturnArgument(0);
        $this->subject = new HashService($this->eventDispatcherProphecy->reveal());
    }

    /**
     * @test
     */
    public function generateRespectsValidUntilDate(): void
    {
        $validUntil = $this->consent->getValidUntil();
        $this->consent->setValidUntil(null);

        $hashWithoutValidUntil = $this->subject->generate($this->consent);
        $this->consent->setValidUntil($validUntil);
        $hashWithValidUntil = $this->subject->generate($this->consent);

        self::assertNotSame($hashWithValidUntil, $hashWithoutValidUntil);
    }

    /**
     * @test
     */
    public function generateRespectsComponentsModifiedThroughEvent(): void
    {
        $hashWithDefaultComponents = $this->subject->generate($this->consent);

        $this->eventDispatcherProphecy->dispatch(Argument::type(GenerateHashEvent::class))->will(function ($args) {
            /** @var GenerateHashEvent $event */
            $event = $args[0];
            $event->setComponents([]);
            return $event;
        });
        $hashWithNoComponents = $this->subject->generate($this->consent);

        self::assertNotSame($hashWithNoComponents, $hashWithDefaultComponents);
    }

    /**
     * @test
     */
    public function generateReturnsCustomHashGeneratedThroughEvent(): void
    {
        $defaultHashGeneration = $this->subject->generate($this->consent);

        $this->eventDispatcherProphecy->dispatch(Argument::type(GenerateHashEvent::class))->will(function ($args) {
            /** @var GenerateHashEvent $event */
            $event = $args[0];
            $event->setHash('foo');
            return $event;
        });
        $customHashGeneration = $this->subject->generate($this->consent);

        self::assertNotSame($customHashGeneration, $defaultHashGeneration);
        self::assertSame('foo', $customHashGeneration);
    }

    /**
     * @test
     */
    public function isValidReturnsTrueIfGeneratedHashEqualsConsentValidationHash(): void
    {
        $this->consent->setValidationHash($this->subject->generate($this->consent));
        self::assertTrue($this->subject->isValid($this->consent));
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfConsentHasChangedInTheMeantime(): void
    {
        $this->subject->generate($this->consent);
        $this->consent->setValidUntil(null);
        self::assertFalse($this->subject->isValid($this->consent));
    }

    /**
     * @test
     */
    public function isValidReturnsCorrectStateForGivenHashAndConsent(): void
    {
        $this->subject->generate($this->consent);
        self::assertFalse($this->subject->isValid($this->consent, 'dummy'));

        $clonedConsent = clone $this->consent;
        $hash = $this->subject->generate($clonedConsent);
        self::assertTrue($this->subject->isValid($this->consent, $hash));
    }

    /**
     * @test
     */
    public function isValidRespectsInitialHashModificationThroughEvent(): void
    {
        $this->eventDispatcherProphecy->dispatch(Argument::type(GenerateHashEvent::class))->will(function ($args) {
            /** @var GenerateHashEvent $event */
            $event = $args[0];
            $event->setComponents([]);
            return $event;
        });
        $hash = $this->subject->generate($this->consent);
        $this->consent->setValidationHash($hash);
        self::assertTrue($this->subject->isValid($this->consent));
    }
}
