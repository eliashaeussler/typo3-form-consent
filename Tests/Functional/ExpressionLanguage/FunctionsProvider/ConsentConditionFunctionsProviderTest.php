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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\ExpressionLanguage\FunctionsProvider;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Form;
use TYPO3\TestingFramework;

/**
 * ConsentConditionFunctionsProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\ExpressionLanguage\FunctionsProvider\ConsentConditionFunctionsProvider::class)]
final class ConsentConditionFunctionsProviderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    protected bool $initializeDatabase = false;

    protected Form\Domain\Runtime\FormRuntime $formRuntime;

    protected function setUp(): void
    {
        parent::setUp();

        $formDefinition = new Form\Domain\Model\FormDefinition('foo', [], 'Form', 'foo');

        $this->formRuntime = $this->get(Form\Domain\Runtime\FormRuntime::class);
        $this->formRuntime->setFormDefinition($formDefinition);
    }

    #[Framework\Attributes\Test]
    public function isConsentApprovedReturnsFalseIfFormRuntimeIsNotDefined(): void
    {
        $resolver = $this->createResolver();

        self::assertFalse($resolver->evaluate('isConsentApproved()'));
    }

    #[Framework\Attributes\Test]
    public function isConsentApprovedFunctionReturnsTrueIfConsentIsRegisteredAndApproved(): void
    {
        $resolver = $this->createResolver($this->formRuntime);

        $consent = new Src\Domain\Model\Consent();
        $consent->setApproved();
        $consent->setFormPersistenceIdentifier('foo');

        Src\Registry\ConsentManagerRegistry::registerConsent($consent);

        self::assertTrue($resolver->evaluate('isConsentApproved()'));
    }

    #[Framework\Attributes\Test]
    public function isConsentDismissedReturnsFalseIfFormRuntimeIsNotDefined(): void
    {
        $resolver = $this->createResolver();

        self::assertFalse($resolver->evaluate('isConsentDismissed()'));
    }

    #[Framework\Attributes\Test]
    public function isConsentDismissedFunctionReturnsTrueIfConsentIsRegisteredAndDismissed(): void
    {
        $resolver = $this->createResolver($this->formRuntime);

        $consent = new Src\Domain\Model\Consent();
        $consent->setDismissed();
        $consent->setFormPersistenceIdentifier('foo');

        Src\Registry\ConsentManagerRegistry::registerConsent($consent);

        self::assertTrue($resolver->evaluate('isConsentDismissed()'));
    }

    private function createResolver(?Form\Domain\Runtime\FormRuntime $formRuntime = null): Core\ExpressionLanguage\Resolver
    {
        $variables = [];

        if ($formRuntime !== null) {
            $variables['formRuntime'] = $formRuntime;
        }

        return new Core\ExpressionLanguage\Resolver('form', $variables);
    }
}
