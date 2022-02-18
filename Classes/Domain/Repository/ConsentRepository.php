<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
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
 *
 * @extends Repository<Consent>
 */
class ConsentRepository extends Repository
{
    public function initializeObject(): void
    {
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
