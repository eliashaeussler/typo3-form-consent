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

namespace EliasHaeussler\Typo3FormConsent\Exception;

use EliasHaeussler\Typo3FormConsent\Type\ConsentStateType;

/**
 * InvalidStateException
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class InvalidStateException extends \Exception
{
    /**
     * @param list<int>|null $supportedStates
     */
    public static function create(int $state, array $supportedStates = null): self
    {
        $supportedStates ??= [
            ConsentStateType::NEW,
            ConsentStateType::APPROVED,
            ConsentStateType::DISMISSED,
        ];

        return new self(
            sprintf(
                'The given state "%d" is invalid. Only states "%s" are supported.',
                $state,
                implode('", "', $supportedStates)
            ),
            1648199643
        );
    }

    /**
     * @param mixed $state
     */
    public static function forTypeMismatch($state): self
    {
        return new self(
            sprintf('Expected a valid state, provided as integer or numeric string, %s given.', get_debug_type($state)),
            1648199565
        );
    }
}
