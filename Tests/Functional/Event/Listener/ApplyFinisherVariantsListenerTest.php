<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Event\Listener;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use Psr\EventDispatcher;
use TYPO3\CMS\Core;
use TYPO3\CMS\Form\Mvc;
use TYPO3\TestingFramework;

/**
 * ApplyFinisherVariantsListenerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Event\Listener\ApplyFinisherVariantsListener::class)]
final class ApplyFinisherVariantsListenerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    protected bool $initializeDatabase = false;

    protected EventDispatcher\EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = $this->get(EventDispatcher\EventDispatcherInterface::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        parent::tearDown();
    }

    #[Framework\Attributes\Test]
    public function listenerAppliesConsentVariantsOnFrontendRequests(): void
    {
        $this->initializeRequest(Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $formDefinition = $this->dispatchEvent();

        self::assertSame(
            ['Consent'],
            array_column($formDefinition['finishers'], 'identifier'),
        );
        self::assertSame(
            Src\Enums\ConsentCondition::Approval->condition(),
            $formDefinition['variants'][0]['condition'],
        );
        self::assertSame(
            ['EmailToReceiver'],
            array_column($formDefinition['variants'][0]['finishers'], 'identifier'),
        );
    }

    #[Framework\Attributes\Test]
    public function listenerDoesNotApplyConsentVariantsOnBackendRequests(): void
    {
        $this->initializeRequest(Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE);

        self::assertSame($this->formDefinition(), $this->dispatchEvent());
    }

    #[Framework\Attributes\Test]
    public function listenerDoesNotApplyConsentVariantsIfRequestIsMissing(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        self::assertSame($this->formDefinition(), $this->dispatchEvent());
    }

    /**
     * @return array<string, mixed>
     */
    private function dispatchEvent(): array
    {
        $event = new Mvc\Persistence\Event\AfterFormDefinitionLoadedEvent(
            $this->formDefinition(),
            'contact',
            'ext-form-load-contact',
        );

        $this->eventDispatcher->dispatch($event);

        return $event->getFormDefinition();
    }

    private function initializeRequest(int $applicationType): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new Core\Http\ServerRequest('https://typo3-testing.local/'))
            ->withAttribute('applicationType', $applicationType);
    }

    /**
     * @return array<string, mixed>
     */
    private function formDefinition(): array
    {
        return [
            'identifier' => 'contact',
            'type' => 'Form',
            'finishers' => [
                [
                    'identifier' => 'Consent',
                    'options' => [],
                ],
                [
                    'identifier' => 'EmailToReceiver',
                    'options' => [
                        'consentCondition' => Src\Enums\ConsentCondition::Approval->value,
                    ],
                ],
            ],
        ];
    }
}
