<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Compatibility\Migration;

use TYPO3\CMS\Core;

/**
 * HmacHashMigration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class HmacHashMigration
{
    public function __construct(
        private Core\Crypto\HashService $hashService,
    ) {}

    public function migrate(string $string, string $secret): string
    {
        // Early return if string does not have HMAC appended
        if (strlen($string) < 40) {
            return $string;
        }

        // Early return if secret is empty
        if ($secret === '') {
            return $string;
        }

        // Validate hash
        try {
            $this->hashService->validateAndStripHmac($string, $secret);

            // Hash is valid
            return $string;
        } catch (Core\Exception\Crypto\InvalidHashStringException) {
            // Hash is invalid and needs migration
        }

        $string = substr($string, 0, -40);

        return $this->hashService->appendHmac($string, $secret);
    }
}
