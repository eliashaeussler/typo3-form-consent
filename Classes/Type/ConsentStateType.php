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

namespace EliasHaeussler\Typo3FormConsent\Type;

use EliasHaeussler\Typo3FormConsent\Exception\InvalidStateException;
use TYPO3\CMS\Core\Type\TypeInterface;

/**
 * ConsentState
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentStateType implements TypeInterface, \Stringable
{
    public const NEW = 0;
    public const APPROVED = 1;
    public const DISMISSED = 2;

    private readonly int $state;

    /**
     * @throws InvalidStateException
     */
    public function __construct(string|int $state = ConsentStateType::NEW)
    {
        if (\is_string($state)) {
            $state = (int)$state;
        }
        if (!$this->isValid($state)) {
            throw InvalidStateException::create($state);
        }

        $this->state = $state;
    }

    public static function createNew(): self
    {
        return new self(self::NEW);
    }

    public static function createApproved(): self
    {
        return new self(self::APPROVED);
    }

    public static function createDismissed(): self
    {
        return new self(self::DISMISSED);
    }

    public function isNew(): bool
    {
        return $this->state === self::NEW;
    }

    public function isApproved(): bool
    {
        return $this->state === self::APPROVED;
    }

    public function isDismissed(): bool
    {
        return $this->state === self::DISMISSED;
    }

    public function __toString(): string
    {
        return (string)$this->state;
    }

    private function isValid(int $state): bool
    {
        return \in_array($state, [
            self::NEW,
            self::APPROVED,
            self::DISMISSED,
        ], true);
    }
}
