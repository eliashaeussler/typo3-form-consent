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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Type;

use EliasHaeussler\Typo3FormConsent\Type\JsonType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * JsonTypeTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[CoversClass(JsonType::class)]
final class JsonTypeTest extends UnitTestCase
{
    /**
     * @var JsonType<string, string>
     */
    protected JsonType $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new JsonType('{"foo":"baz"}');
    }

    #[Test]
    public function fromArrayReturnsObjectWithJsonEncodedData(): void
    {
        self::assertEquals($this->subject, JsonType::fromArray(['foo' => 'baz']));
    }

    #[Test]
    public function objectIsJsonSerializable(): void
    {
        self::assertSame('{"foo":"baz"}', json_encode($this->subject, JSON_THROW_ON_ERROR));
    }

    #[Test]
    public function stringRepresentationEqualsJsonRepresentation(): void
    {
        self::assertSame('{"foo":"baz"}', (string)$this->subject);
    }

    #[Test]
    public function toArrayReturnsArrayRepresentation(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->toArray());
    }

    #[Test]
    public function objectCanBeAccessedAsArray(): void
    {
        // offsetExists()
        self::assertArrayHasKey('foo', $this->subject);
        self::assertArrayNotHasKey('baz', $this->subject);

        // offsetGet()
        self::assertSame('baz', $this->subject['foo']);
        self::assertNull($this->subject['baz']);

        // offsetSet()
        $this->subject['baz'] = 'foo';
        self::assertSame('foo', $this->subject['baz']);

        // offsetUnset()
        unset($this->subject['baz']);
        self::assertNull($this->subject['baz']);
    }
}
