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

namespace EliasHaeussler\Typo3FormConsent\Registry;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Registry\Dto\ConsentState;

/**
 * ConsentManagerRegistry
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ConsentManagerRegistry
{
    /**
     * @var array<string, array<int, ConsentState>>
     */
    private static $states = [];

    public static function registerConsent(Consent $consent): ConsentState
    {
        return self::$states[$consent->getFormPersistenceIdentifier()][(int)$consent->getUid()] = new ConsentState($consent);
    }

    public static function unregisterConsent(Consent $consent): void
    {
        unset(self::$states[$consent->getFormPersistenceIdentifier()][(int)$consent->getUid()]);
    }

    public static function isConsentApproved(string $formPersistenceIdentifier): bool
    {
        foreach (self::$states[$formPersistenceIdentifier] ?? [] as $state) {
            if ($state->isApproved()) {
                return true;
            }
        }

        return false;
    }

    public static function isConsentDismissed(string $formPersistenceIdentifier): bool
    {
        foreach (self::$states[$formPersistenceIdentifier] ?? [] as $state) {
            if ($state->isDismissed()) {
                return true;
            }
        }

        return false;
    }
}
