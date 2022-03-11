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

namespace EliasHaeussler\Typo3FormConsent\Type;

use TYPO3\CMS\Core\Type\TypeInterface;

/**
 * JsonType
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @template TKey
 * @template TValue
 * @implements \ArrayAccess<TKey, TValue>
 */
final class JsonType implements TypeInterface, \ArrayAccess, \JsonSerializable
{
    /**
     * @var array<TKey, TValue>
     */
    private array $data;

    public function __construct(string $json)
    {
        $this->data = json_decode($json, true);
    }

    /**
     * @param array<TKey, TValue> $data
     * @return self<TKey, TValue>
     * @throws \JsonException
     */
    public static function fromArray(array $data): self
    {
        return new self(json_encode($data, JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return array<TKey, TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param TKey $offset
     * @return TValue|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @param TKey $offset
     * @param TValue $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param TKey $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
