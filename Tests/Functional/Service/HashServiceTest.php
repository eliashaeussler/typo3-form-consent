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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Service;

use DateTime;
use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use Psr\EventDispatcher;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * HashServiceTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Service\HashService::class)]
final class HashServiceTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected Src\Domain\Model\Consent $consent;
    protected Core\EventDispatcher\ListenerProvider $listenerProvider;
    protected Src\Service\HashService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consent = (new Src\Domain\Model\Consent())
            ->setEmail('dummy@example.com')
            ->setDate(new DateTime())
            ->setData(Src\Type\JsonType::fromArray(['foo' => 'baz']))
            ->setValidUntil(new DateTime());

        $this->listenerProvider = $this->getContainer()->get(Core\EventDispatcher\ListenerProvider::class);
        $this->subject = new Src\Service\HashService($this->getContainer()->get(EventDispatcher\EventDispatcherInterface::class));
    }

    #[Framework\Attributes\Test]
    public function generateRespectsValidUntilDate(): void
    {
        $validUntil = $this->consent->getValidUntil();
        $this->consent->setValidUntil(null);

        $hashWithoutValidUntil = $this->subject->generate($this->consent);
        $this->consent->setValidUntil($validUntil);
        $hashWithValidUntil = $this->subject->generate($this->consent);

        self::assertNotSame($hashWithValidUntil, $hashWithoutValidUntil);
    }

    #[Framework\Attributes\Test]
    public function generateRespectsComponentsModifiedThroughEvent(): void
    {
        $hashWithDefaultComponents = $this->subject->generate($this->consent);

        $this->addEventListener(
            Src\Event\GenerateHashEvent::class,
            __METHOD__,
            new class () {
                public function __invoke(Src\Event\GenerateHashEvent $event): void
                {
                    $event->setComponents([]);
                }
            }
        );

        $hashWithNoComponents = $this->subject->generate($this->consent);

        self::assertNotSame($hashWithNoComponents, $hashWithDefaultComponents);
    }

    #[Framework\Attributes\Test]
    public function generateReturnsCustomHashGeneratedThroughEvent(): void
    {
        $defaultHashGeneration = $this->subject->generate($this->consent);

        $this->addEventListener(
            Src\Event\GenerateHashEvent::class,
            __METHOD__,
            new class () {
                public function __invoke(Src\Event\GenerateHashEvent $event): void
                {
                    $event->setHash('foo');
                }
            }
        );

        $customHashGeneration = $this->subject->generate($this->consent);

        self::assertNotSame($customHashGeneration, $defaultHashGeneration);
        self::assertSame('foo', $customHashGeneration);
    }

    #[Framework\Attributes\Test]
    public function isValidReturnsTrueIfGeneratedHashEqualsConsentValidationHash(): void
    {
        $this->consent->setValidationHash($this->subject->generate($this->consent));
        self::assertTrue($this->subject->isValid($this->consent));
    }

    #[Framework\Attributes\Test]
    public function isValidReturnsFalseIfConsentHasChangedInTheMeantime(): void
    {
        $this->subject->generate($this->consent);
        $this->consent->setValidUntil(null);
        self::assertFalse($this->subject->isValid($this->consent));
    }

    #[Framework\Attributes\Test]
    public function isValidReturnsCorrectStateForGivenHashAndConsent(): void
    {
        $this->subject->generate($this->consent);
        self::assertFalse($this->subject->isValid($this->consent, 'dummy'));

        $clonedConsent = clone $this->consent;
        $hash = $this->subject->generate($clonedConsent);
        self::assertTrue($this->subject->isValid($this->consent, $hash));
    }

    #[Framework\Attributes\Test]
    public function isValidRespectsInitialHashModificationThroughEvent(): void
    {
        $this->addEventListener(
            Src\Event\GenerateHashEvent::class,
            __METHOD__,
            new class () {
                public function __invoke(Src\Event\GenerateHashEvent $event): void
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

        self::assertInstanceOf(DependencyInjection\Container::class, $container);

        $container->set($service, $object);
        $this->listenerProvider->addListener($event, $service);
    }
}
