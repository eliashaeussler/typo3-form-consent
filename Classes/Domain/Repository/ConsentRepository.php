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

namespace EliasHaeussler\Typo3FormConsent\Domain\Repository;

use EliasHaeussler\Typo3FormConsent\Domain;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

/**
 * ConsentRepository
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 *
 * @extends Extbase\Persistence\Repository<Domain\Model\Consent>
 */
class ConsentRepository extends Extbase\Persistence\Repository
{
    public function initializeObject(): void
    {
        $querySettings = Core\Utility\GeneralUtility::makeInstance(Extbase\Persistence\Generic\Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    public function findOneByValidationHash(string $validationHash): ?Domain\Model\Consent
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('validation_hash', $validationHash),
                $query->logicalOr(
                    $query->equals('valid_until', 0),
                    $query->greaterThanOrEqual('valid_until', time()),
                ),
            ),
        );
        /** @var Domain\Model\Consent|null $consent */
        $consent = $query->execute()->getFirst();

        return $consent;
    }
}
