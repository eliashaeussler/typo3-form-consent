<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Type;

use EliasHaeussler\Typo3FormConsent\Enums;
use TYPO3\CMS\Core;

/**
 * ConsentState
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class ConsentStateType implements Core\Type\TypeInterface, \Stringable
{
    private Enums\ConsentState $state;

    public function __construct(string|int|Enums\ConsentState $state = Enums\ConsentState::New)
    {
        if (\is_string($state)) {
            $state = (int)$state;
        }
        if (\is_int($state)) {
            $state = Enums\ConsentState::from($state);
        }

        $this->state = $state;
    }

    public static function createNew(): self
    {
        return new self(Enums\ConsentState::New);
    }

    public static function createApproved(): self
    {
        return new self(Enums\ConsentState::Approved);
    }

    public static function createDismissed(): self
    {
        return new self(Enums\ConsentState::Dismissed);
    }

    public function isNew(): bool
    {
        return $this->state === Enums\ConsentState::New;
    }

    public function isApproved(): bool
    {
        return $this->state === Enums\ConsentState::Approved;
    }

    public function isDismissed(): bool
    {
        return $this->state === Enums\ConsentState::Dismissed;
    }

    public function __toString(): string
    {
        return (string)$this->state->value;
    }
}
