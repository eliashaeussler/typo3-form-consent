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

namespace EliasHaeussler\Typo3FormConsent\Widget\Provider;

use EliasHaeussler\Typo3FormConsent\Domain;
use EliasHaeussler\Typo3FormConsent\Enums;
use TYPO3\CMS\Core;
use TYPO3\CMS\Dashboard;

/**
 * ConsentChartDataProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentChartDataProvider implements Dashboard\Widgets\ChartDataProviderInterface
{
    private ?Core\Localization\LanguageService $languageService = null;

    public function __construct(
        private readonly Core\Database\Connection $connection,
        private readonly Core\Localization\LanguageServiceFactory $languageServiceFactory,
    ) {}

    /**
     * @return array{labels: list<string>, datasets: array{array{backgroundColor: list<string>, data: list<int>}}}
     */
    public function getChartData(): array
    {
        return [
            'labels' => [
                $this->translate('LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:charts.approved'),
                $this->translate('LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:charts.nonApproved'),
                $this->translate('LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:charts.dismissed'),
            ],
            'datasets' => [
                [
                    'backgroundColor' => Dashboard\WidgetApi::getDefaultChartColors(),
                    'data' => [$this->countApproved(), $this->countNonApproved(), $this->countDismissed()],
                ],
            ],
        ];
    }

    private function countApproved(): int
    {
        return $this->count(Enums\ConsentState::Approved);
    }

    private function countNonApproved(): int
    {
        return $this->count(Enums\ConsentState::New);
    }

    private function countDismissed(): int
    {
        return $this->count(Enums\ConsentState::Dismissed, true);
    }

    private function count(Enums\ConsentState $state, bool $includeDeleted = false): int
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();

        if (!$includeDeleted) {
            $queryBuilder->getRestrictions()->add(Core\Utility\GeneralUtility::makeInstance(Core\Database\Query\Restriction\DeletedRestriction::class));
        }

        $result = $queryBuilder->count('*')
            ->from(Domain\Model\Consent::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'state',
                    $queryBuilder->createNamedParameter($state->value, Core\Database\Connection::PARAM_INT),
                ),
            )
            ->executeQuery();

        return (int)$result->fetchOne();
    }

    private function translate(string $key): string
    {
        $this->languageService ??= $this->languageServiceFactory->createFromUserPreferences($this->getBackendUser());

        return $this->languageService->sL($key);
    }

    private function getBackendUser(): Core\Authentication\BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
