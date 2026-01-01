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

namespace EliasHaeussler\Typo3FormConsent\Compatibility\Migration;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;

/**
 * HmacHashMigration
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class HmacHashMigration
{
    private const LEGACY_HASH_LENGTH = 40;

    private Core\Information\Typo3Version $typo3Version;

    public function __construct(
        private Core\Crypto\HashService $hashService,
    ) {
        $this->typo3Version = new Core\Information\Typo3Version();
    }

    public function migrate(string $string, Extbase\Security\HashScope|Form\Security\HashScope $hashScope): string
    {
        // Early return if string does not have HMAC appended
        if (strlen($string) < self::LEGACY_HASH_LENGTH) {
            return $string;
        }

        // Validate hash
        try {
            $this->validateHashedString($string, $hashScope);

            // Hash is valid
            return $string;
        } catch (Core\Exception\Crypto\InvalidHashStringException) {
            // Hash is invalid and needs migration
        }

        /** @var non-empty-string $string */
        $string = substr($string, 0, -self::LEGACY_HASH_LENGTH);

        return $this->migrateString($string, $hashScope);
    }

    /**
     * @param non-empty-string $string
     */
    private function validateHashedString(string $string, Extbase\Security\HashScope|Form\Security\HashScope $hashScope): void
    {
        if ($this->typo3Version->getMajorVersion() === 13) {
            // @todo Remove once support for TYPO3 v13 is dropped
            $this->hashService->validateAndStripHmac($string, $hashScope->prefix());
        } else {
            $this->hashService->validateAndStripHmac($string, $hashScope->prefix(), $this->getRequiredHashAlgo($hashScope));
        }
    }

    /**
     * @param non-empty-string $string
     */
    private function migrateString(string $string, Extbase\Security\HashScope|Form\Security\HashScope $hashScope): string
    {
        // @todo Remove once support for TYPO3 v13 is dropped
        if ($this->typo3Version->getMajorVersion() === 13) {
            return $this->hashService->appendHmac($string, $hashScope->prefix());
        }

        return $this->hashService->appendHmac($string, $hashScope->prefix(), $this->getRequiredHashAlgo($hashScope));
    }

    private function getRequiredHashAlgo(Extbase\Security\HashScope|Form\Security\HashScope $hashScope): ?Core\Crypto\HashAlgo
    {
        if ($this->typo3Version->getMajorVersion() === 13) {
            return null;
        }

        if ($hashScope === Form\Security\HashScope::ResourcePointer) {
            return Core\Crypto\HashAlgo::SHA1;
        }

        return Core\Crypto\HashAlgo::SHA3_256;
    }
}
