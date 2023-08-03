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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\ExpressionLanguage\FunctionsProvider;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\ExpressionLanguage\FunctionsProvider\ConsentConditionFunctionsProvider;
use EliasHaeussler\Typo3FormConsent\Registry\ConsentManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * ConsentConditionFunctionsProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[CoversClass(ConsentConditionFunctionsProvider::class)]
final class ConsentConditionFunctionsProviderTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    protected bool $initializeDatabase = false;

    protected FormRuntime $formRuntime;

    protected function setUp(): void
    {
        parent::setUp();

        $formDefinition = new FormDefinition('foo', [], 'Form', 'foo');

        $this->formRuntime = $this->get(FormRuntime::class);
        $this->formRuntime->setFormDefinition($formDefinition);
    }

    #[Test]
    public function isConsentApprovedReturnsFalseIfFormRuntimeIsNotDefined(): void
    {
        $resolver = $this->createResolver();

        self::assertFalse($resolver->evaluate('isConsentApproved()'));
    }

    #[Test]
    public function isConsentApprovedFunctionReturnsTrueIfConsentIsRegisteredAndApproved(): void
    {
        $resolver = $this->createResolver($this->formRuntime);

        $consent = new Consent();
        $consent->setApproved();
        $consent->setFormPersistenceIdentifier('foo');

        ConsentManagerRegistry::registerConsent($consent);

        self::assertTrue($resolver->evaluate('isConsentApproved()'));
    }

    #[Test]
    public function isConsentDismissedReturnsFalseIfFormRuntimeIsNotDefined(): void
    {
        $resolver = $this->createResolver();

        self::assertFalse($resolver->evaluate('isConsentDismissed()'));
    }

    #[Test]
    public function isConsentDismissedFunctionReturnsTrueIfConsentIsRegisteredAndDismissed(): void
    {
        $resolver = $this->createResolver($this->formRuntime);

        $consent = new Consent();
        $consent->setDismissed();
        $consent->setFormPersistenceIdentifier('foo');

        ConsentManagerRegistry::registerConsent($consent);

        self::assertTrue($resolver->evaluate('isConsentDismissed()'));
    }

    private function createResolver(FormRuntime $formRuntime = null): Resolver
    {
        $variables = [];

        if ($formRuntime !== null) {
            $variables['formRuntime'] = $formRuntime;
        }

        return new Resolver('form', $variables);
    }
}
