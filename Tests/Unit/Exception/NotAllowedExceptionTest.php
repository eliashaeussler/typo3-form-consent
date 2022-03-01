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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Exception;

use EliasHaeussler\Typo3FormConsent\Exception\NotAllowedException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * NotAllowedExceptionTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class NotAllowedExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function forMethodReturnsNotAllowedExceptionForGivenMethod(): void
    {
        $actual = NotAllowedException::forMethod('foo');

        self::assertInstanceOf(NotAllowedException::class, $actual);
        self::assertSame('Calling the method "foo" is not allowed.', $actual->getMessage());
        self::assertSame(1645781267, $actual->getCode());
    }
}
