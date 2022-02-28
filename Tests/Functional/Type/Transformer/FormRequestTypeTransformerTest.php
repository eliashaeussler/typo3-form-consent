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

use EliasHaeussler\Typo3FormConsent\Type\Transformer\FormRequestTypeTransformer;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * FormRequestTypeTransformerTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class FormRequestTypeTransformerTest extends FunctionalTestCase
{
    protected FormRequestTypeTransformer $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FormRequestTypeTransformer($this->getContainer()->get(HashService::class));
    }

    /**
     * @test
     */
    public function transformThrowsExceptionIfFormRuntimeIsNotGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1646044629);
        $this->expectExceptionMessage('Expected a valid FormRuntime object, but gut none.');

        $this->subject->transform();
    }
}
