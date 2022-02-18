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

namespace EliasHaeussler\Typo3FormConsent\Widget\Provider;

use Doctrine\DBAL\Result;
use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Dashboard\WidgetApi;
use TYPO3\CMS\Dashboard\Widgets\ChartDataProviderInterface;

/**
 * ConsentChartDataProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConsentChartDataProvider implements ChartDataProviderInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array{labels: array<string>, datasets: array{0: array{backgroundColor: array<string>, data: array<int>}}}
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

    protected function countApproved(): int
    {
        return $this->count(true, false);
    }

    protected function countNonApproved(): int
    {
        return $this->count(false, false);
    }

    protected function countDismissed(): int
    {
        return $this->count(false, true);
    }

    protected function count(bool $approved, bool $deleted): int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        /** @var Result $result */
        $result = $queryBuilder->count('*')
            ->from(Consent::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('approved', $queryBuilder->createNamedParameter($approved, Connection::PARAM_BOOL)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter($deleted, Connection::PARAM_BOOL))
            )
            ->execute();

        return (int)$result->fetchOne();
    }
}
