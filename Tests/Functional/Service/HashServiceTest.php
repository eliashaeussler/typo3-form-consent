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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Service;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Event\GenerateHashEvent;
use EliasHaeussler\Typo3FormConsent\Service\HashService;
use EliasHaeussler\Typo3FormConsent\Type\JsonType;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * HashServiceTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class HashServiceTest extends FunctionalTestCase
{
    protected Consent $consent;
    protected ListenerProvider $listenerProvider;
    protected HashService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consent = (new Consent())
            ->setEmail('dummy@example.com')
            ->setDate(new \DateTime())
            ->setData(JsonType::fromArray(['foo' => 'baz']))
            ->setValidUntil(new \DateTime());

        $this->listenerProvider = $this->getContainer()->get(ListenerProvider::class);
        $this->subject = new HashService($this->getContainer()->get(EventDispatcherInterface::class));
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

        $this->addEventListener(
            GenerateHashEvent::class,
            __METHOD__,
            new class() {
                public function __invoke(GenerateHashEvent $event): void
                {
                    $event->setComponents([]);
                }
            }
        );

        $hashWithNoComponents = $this->subject->generate($this->consent);

        self::assertNotSame($hashWithNoComponents, $hashWithDefaultComponents);
    }

    /**
     * @test
     */
    public function generateReturnsCustomHashGeneratedThroughEvent(): void
    {
        $defaultHashGeneration = $this->subject->generate($this->consent);

        $this->addEventListener(
            GenerateHashEvent::class,
            __METHOD__,
            new class() {
                public function __invoke(GenerateHashEvent $event): void
                {
                    $event->setHash('foo');
                }
            }
        );

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
        $this->addEventListener(
            GenerateHashEvent::class,
            __METHOD__,
            new class() {
                public function __invoke(GenerateHashEvent $event): void
                {
                    $event->setComponents([]);
                }
            }
        );

        $hash = $this->subject->generate($this->consent);
        $this->consent->setValidationHash($hash);

        self::assertTrue($this->subject->isValid($this->consent));
    }

    private function addEventListener(string $event, string $service, object $object): void
    {
        $container = $this->getContainer();

        self::assertInstanceOf(Container::class, $container);

        $container->set($service, $object);
        $this->listenerProvider->addListener($event, $service);
    }
}
