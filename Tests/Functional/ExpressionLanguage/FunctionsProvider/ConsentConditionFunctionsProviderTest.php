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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\ExpressionLanguage\FunctionsProvider;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Registry\ConsentManagerRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
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
final class ConsentConditionFunctionsProviderTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected $coreExtensionsToLoad = [
        'form',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/form_consent',
    ];
    /**
     * @var FormRuntime|ObjectProphecy
     */
    protected $formRuntimeProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $formDefinition = new FormDefinition('foo', [], 'Form', 'foo');

        // @todo Replace with $this->getContainer()->get(FormRuntime::class) once v10 support is dropped
        $this->formRuntimeProphecy = $this->prophesize(FormRuntime::class);
        $this->formRuntimeProphecy->getFormDefinition()->willReturn($formDefinition);
    }

    /**
     * @test
     */
    public function isConsentApprovedReturnsFalseIfFormRuntimeIsNotDefined(): void
    {
        $resolver = $this->createResolver();

        self::assertFalse($resolver->evaluate('isConsentApproved()'));
    }

    /**
     * @test
     */
    public function isConsentApprovedFunctionReturnsTrueIfConsentIsRegisteredAndApproved(): void
    {
        $resolver = $this->createResolver($this->formRuntimeProphecy->reveal());

        $consent = new Consent();
        $consent->setApproved(true);
        $consent->setFormPersistenceIdentifier('foo');

        ConsentManagerRegistry::registerConsent($consent);

        self::assertTrue($resolver->evaluate('isConsentApproved()'));
    }

    /**
     * @test
     */
    public function isConsentDismissedReturnsFalseIfFormRuntimeIsNotDefined(): void
    {
        $resolver = $this->createResolver();

        self::assertFalse($resolver->evaluate('isConsentDismissed()'));
    }

    /**
     * @test
     */
    public function isConsentDismissedFunctionReturnsTrueIfConsentIsRegisteredAndDismissed(): void
    {
        $resolver = $this->createResolver($this->formRuntimeProphecy->reveal());

        $consent = new Consent();
        $consent->setApproved(false);
        $consent->setData(null);
        $consent->setOriginalRequestParameters(null);
        $consent->setFormPersistenceIdentifier('foo');

        ConsentManagerRegistry::registerConsent($consent);

        self::assertTrue($resolver->evaluate('isConsentDismissed()'));
    }

    private function createResolver(FormRuntime $formRuntime = null): Resolver
    {
        $variables = [];

        if (null !== $formRuntime) {
            $variables['formRuntime'] = $formRuntime;
        }

        return new Resolver('form', $variables);
    }
}
