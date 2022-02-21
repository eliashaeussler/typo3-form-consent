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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Type\Transformer;

use EliasHaeussler\Typo3FormConsent\Exception\UnsupportedTypeException;
use EliasHaeussler\Typo3FormConsent\Type\Transformer\FormRequestTypeTransformer;
use EliasHaeussler\Typo3FormConsent\Type\Transformer\TypeTransformerFactory;
use Symfony\Component\DependencyInjection\ServiceLocator;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * TypeTransformerFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class TypeTransformerFactoryTest extends UnitTestCase
{
    /**
     * @var FormRequestTypeTransformer
     */
    protected $formRequestTypeTransformer;

    /**
     * @var TypeTransformerFactory
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formRequestTypeTransformer = new FormRequestTypeTransformer(new HashService());
        $this->subject = new TypeTransformerFactory(new ServiceLocator([
            FormRequestTypeTransformer::getName() => function (): FormRequestTypeTransformer {
                return $this->formRequestTypeTransformer;
            },
            'invalid' => function (): self {
                return $this;
            },
        ]));
    }

    /**
     * @test
     */
    public function getThrowsExceptionIfRequestedTypeTransformerIsNotAvailable(): void
    {
        $this->expectException(UnsupportedTypeException::class);
        $this->expectExceptionCode(1645774926);
        $this->expectExceptionMessage('The type "foo" is not supported.');

        $this->subject->get('foo');
    }

    /**
     * @test
     */
    public function getThrowsExceptionIfRequestedTypeTransformerIsInvalid(): void
    {
        $this->expectException(UnsupportedTypeException::class);
        $this->expectExceptionCode(1645774926);
        $this->expectExceptionMessage('The type "invalid" is not supported.');

        $this->subject->get('invalid');
    }

    /**
     * @test
     */
    public function getReturnsRequestedTypeTransformer(): void
    {
        self::assertSame($this->formRequestTypeTransformer, $this->subject->get(FormRequestTypeTransformer::getName()));
    }
}
