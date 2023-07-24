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

namespace EliasHaeussler\Typo3FormConsent\Widget\Provider;

use Doctrine\DBAL\Result;
use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Type\ConsentStateType;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

/**
 * ConsentChartDataProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentChartDataProvider implements ChartDataProviderInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return array{labels: list<string>, datasets: array{array{backgroundColor: list<string>, data: list<int>}}}
     */
    public function getChartData(): array
    {
        return [
            'labels' => [
                Localization::forChart('approved', true),
                Localization::forChart('nonApproved', true),
                Localization::forChart('dismissed', true),
            ],
            'datasets' => [
                [
                    'backgroundColor' => WidgetApi::getDefaultChartColors(),
                    'data' => [$this->countApproved(), $this->countNonApproved(), $this->countDismissed()],
                ],
            ],
        ];
    }

    private function countApproved(): int
    {
        return $this->count(ConsentStateType::APPROVED);
    }

    private function countNonApproved(): int
    {
        return $this->count(ConsentStateType::NEW);
    }

    private function countDismissed(): int
    {
        return $this->count(ConsentStateType::DISMISSED, true);
    }

    private function count(int $state, bool $includeDeleted = false): int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        if (!$includeDeleted) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        }

        /** @var Result $result */
        $result = $queryBuilder->count('*')
            ->from(Consent::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'state',
                    $queryBuilder->createNamedParameter($state, Connection::PARAM_INT)
                )
            )
            ->execute();

        return (int)$result->fetchOne();
    }
}
