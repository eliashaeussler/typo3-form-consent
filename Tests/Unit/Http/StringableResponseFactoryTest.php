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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Http;

use EliasHaeussler\Typo3FormConsent\Http\StringableResponse;
use EliasHaeussler\Typo3FormConsent\Http\StringableResponseFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * StringableResponseFactoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class StringableResponseFactoryTest extends UnitTestCase
{
    /**
     * @test
     */
    public function createResponseReturnsStringableResponseOnTypo3V10(): void
    {
        // @todo Remove test once v10 support is dropped

        if ($this->getMajorTypo3Version() > 10) {
            self::markTestSkipped(sprintf('Test targets TYPO3 v10, v%d found.', $this->getMajorTypo3Version()));
        }

        $subject = new StringableResponseFactory();

        self::assertInstanceOf(StringableResponse::class, $subject->createResponse());
    }

    /**
     * @test
     */
    public function createResponseReturnsResponseOnTypo3V11(): void
    {
        if ($this->getMajorTypo3Version() < 11) {
            self::markTestSkipped(sprintf('Test targets TYPO3 v11, v%d found.', $this->getMajorTypo3Version()));
        }

        $subject = new StringableResponseFactory();

        self::assertInstanceOf(Response::class, $subject->createResponse());
    }

    private function getMajorTypo3Version(): int
    {
        return (new Typo3Version())->getMajorVersion();
    }
}
