<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Domain\Repository;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * ConsentRepository
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConsentRepository extends Repository
{
    public function initializeObject(): void
    {
        // @todo check whether this works in v10

        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function findOneByValidationHash(string $validationHash): ?Consent
    {
        $query = $this->createQuery();
        $constraints = [
            $query->equals('validation_hash', $validationHash),
            $query->logicalOr([
                $query->equals('valid_until', null),
                $query->greaterThanOrEqual('valid_until', time()),
            ]),
        ];
        $query->matching($query->logicalAnd($constraints));
        /** @var Consent|null $consent */
        $consent = $query->execute()->getFirst();

        return $consent;
    }
}
