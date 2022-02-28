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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Type\Transformer;

use EliasHaeussler\Typo3FormConsent\Type\JsonType;
use EliasHaeussler\Typo3FormConsent\Type\Transformer\FormValuesTypeTransformer;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * FormValuesTypeTransformerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class FormValuesTypeTransformerTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected $coreExtensionsToLoad = [
        'form',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/form_consent',
    ];

    protected FormValuesTypeTransformer $subject;

    /**
     * @var FormRuntime|ObjectProphecy
     */
    protected $formRuntimeProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FormValuesTypeTransformer(new Context());
        // @todo Replace with $this->getContainer()->get(FormRuntime::class) once v10 support is dropped
        $this->formRuntimeProphecy = $this->prophesize(FormRuntime::class);
    }

    /**
     * @test
     */
    public function transformThrowsExceptionIfFormRuntimeIsNotGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1646044591);
        $this->expectExceptionMessage('Expected a valid FormRuntime object, but gut none.');

        $this->subject->transform();
    }

    /**
     * @test
     */
    public function transformReturnsJsonTypeWithEmptyArrayIfFormIsUninitialized(): void
    {
        $this->formRuntimeProphecy->getFormState()->willReturn(null);

        self::assertEquals(JsonType::fromArray([]), $this->subject->transform($this->formRuntimeProphecy->reveal()));
    }
}
