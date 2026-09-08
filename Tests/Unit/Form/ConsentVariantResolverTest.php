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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Form;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ConsentVariantResolverTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Form\ConsentVariantResolver::class)]
final class ConsentVariantResolverTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Form\ConsentVariantResolver $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Form\ConsentVariantResolver();
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsFormDefinitionUnchangedIfFinishersAreMissing(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'type' => 'Form',
        ];

        self::assertSame($formDefinition, $this->subject->resolve($formDefinition));
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsFormDefinitionUnchangedIfConsentFinisherIsMissing(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
            ],
        ];

        self::assertSame($formDefinition, $this->subject->resolve($formDefinition));
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsFormDefinitionUnchangedIfNoFinisherIsMarkedAsConsentDependent(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('Confirmation'),
            ],
        ];

        self::assertSame($formDefinition, $this->subject->resolve($formDefinition));
    }

    #[Framework\Attributes\Test]
    public function resolveMovesConsentDependentFinishersToGeneratedVariant(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
                self::finisher('Confirmation'),
            ],
        ];

        $expected = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('Confirmation'),
            ],
            'variants' => [
                [
                    'identifier' => Src\Enums\ConsentCondition::Approval->variantIdentifier(),
                    'condition' => Src\Enums\ConsentCondition::Approval->condition(),
                    'finishers' => [
                        self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
                    ],
                ],
            ],
        ];

        self::assertSame($expected, $this->subject->resolve($formDefinition));
    }

    #[Framework\Attributes\Test]
    public function resolveCreatesOneVariantPerConsentCondition(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('Redirect', Src\Enums\ConsentCondition::Dismissal->value),
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
            ],
        ];

        $actual = $this->subject->resolve($formDefinition);

        self::assertSame([self::finisher('Consent')], $actual['finishers']);
        self::assertCount(2, $actual['variants']);
        self::assertSame(
            Src\Enums\ConsentCondition::Approval->condition(),
            $actual['variants'][0]['condition'],
        );
        self::assertSame(
            [self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value)],
            $actual['variants'][0]['finishers'],
        );
        self::assertSame(
            Src\Enums\ConsentCondition::Dismissal->condition(),
            $actual['variants'][1]['condition'],
        );
        self::assertSame(
            [self::finisher('Redirect', Src\Enums\ConsentCondition::Dismissal->value)],
            $actual['variants'][1]['finishers'],
        );
    }

    #[Framework\Attributes\Test]
    public function resolveKeepsManuallyConfiguredVariantsWithPrecedence(): void
    {
        $manualVariant = [
            'identifier' => 'variant-1',
            'condition' => 'isConsentApproved()',
            'finishers' => [
                self::finisher('Closure'),
            ],
        ];

        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
            ],
            'variants' => [
                $manualVariant,
            ],
        ];

        $actual = $this->subject->resolve($formDefinition);

        self::assertCount(2, $actual['variants']);
        self::assertSame(
            Src\Enums\ConsentCondition::Approval->variantIdentifier(),
            $actual['variants'][0]['identifier'],
        );
        self::assertSame($manualVariant, $actual['variants'][1]);
    }

    #[Framework\Attributes\Test]
    public function resolveIgnoresUnknownConsentConditions(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('EmailToReceiver', 'needsSomethingElse'),
            ],
        ];

        self::assertSame($formDefinition, $this->subject->resolve($formDefinition));
    }

    #[Framework\Attributes\Test]
    public function resolveIgnoresConsentConditionOnConsentFinisher(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent', Src\Enums\ConsentCondition::Approval->value),
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
            ],
        ];

        $actual = $this->subject->resolve($formDefinition);

        self::assertSame(
            [self::finisher('Consent', Src\Enums\ConsentCondition::Approval->value)],
            $actual['finishers'],
        );
        self::assertSame(
            [self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value)],
            $actual['variants'][0]['finishers'],
        );
    }

    #[Framework\Attributes\Test]
    public function resolveIgnoresMalformedVariantsConfiguration(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
            ],
            'variants' => 'not-a-list',
        ];

        $actual = $this->subject->resolve($formDefinition);

        self::assertCount(1, $actual['variants']);
        self::assertSame(
            Src\Enums\ConsentCondition::Approval->variantIdentifier(),
            $actual['variants'][0]['identifier'],
        );
    }

    #[Framework\Attributes\Test]
    public function resolveIsIdempotent(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
            ],
        ];

        $resolvedOnce = $this->subject->resolve($formDefinition);

        self::assertSame($resolvedOnce, $this->subject->resolve($resolvedOnce));
    }

    #[Framework\Attributes\Test]
    public function resolvePreservesConfiguredFinisherOrder(): void
    {
        $formDefinition = [
            'identifier' => 'contact',
            'finishers' => [
                self::finisher('Consent'),
                self::finisher('EmailToReceiver', Src\Enums\ConsentCondition::Approval->value),
                self::finisher('DeleteUploads'),
                self::finisher('Redirect', Src\Enums\ConsentCondition::Approval->value),
            ],
        ];

        $actual = $this->subject->resolve($formDefinition);

        self::assertSame(
            ['Consent', 'DeleteUploads'],
            array_column($actual['finishers'], 'identifier'),
        );
        self::assertSame(
            ['EmailToReceiver', 'Redirect'],
            array_column($actual['variants'][0]['finishers'], 'identifier'),
        );
    }

    /**
     * @return array{identifier: string, options: array<string, mixed>}
     */
    private static function finisher(string $identifier, ?string $consentCondition = null): array
    {
        $options = ['subject' => 'dummy'];

        if ($consentCondition !== null) {
            $options['consentCondition'] = $consentCondition;
        }

        return [
            'identifier' => $identifier,
            'options' => $options,
        ];
    }
}
