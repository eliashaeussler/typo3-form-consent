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
final class HmacHashMigration
{
    private readonly Core\Crypto\HashService $coreHashService;

    public function __construct()
    {
        // @todo Use DI once support for TYPO3 v12 is dropped
        $this->coreHashService = Core\Utility\GeneralUtility::makeInstance(Core\Crypto\HashService::class);
    }

    public function migrate(string $string, string $secret): string
    {
        try {
            $this->coreHashService->validateAndStripHmac($string, $secret);

            // Hash is valid
            return $string;
        } catch (Core\Exception\Crypto\InvalidHashStringException) {
            // Hash is invalid and needs migration
        }

        // Early return if string does not have HMAC appended
        if (strlen($string) < 40) {
            return $string;
        }

        $string = substr($string, 0, -40);

        return $this->coreHashService->appendHmac($string, $secret);
    }
}
