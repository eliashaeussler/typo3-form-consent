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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Exception;

use EliasHaeussler\Typo3FormConsent\Exception\UnsupportedTypeException;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * UnsupportedTypeExceptionTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class UnsupportedTypeExceptionTest extends UnitTestCase
{
    #[Test]
    public function createReturnsUnsupportedTypeExceptionForGivenType(): void
    {
        $actual = UnsupportedTypeException::create('foo');

        self::assertInstanceOf(UnsupportedTypeException::class, $actual);
        self::assertSame('The type "foo" is not supported.', $actual->getMessage());
        self::assertSame(1645774926, $actual->getCode());
    }
}
