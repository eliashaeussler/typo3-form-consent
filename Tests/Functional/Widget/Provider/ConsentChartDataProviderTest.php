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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Widget\Provider;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ConsentChartDataProviderTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Widget\Provider\ConsentChartDataProvider::class)]
final class ConsentChartDataProviderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    private static string $languagePrefix = 'LLL:EXT:form_consent/Resources/Private/Language/locallang_be.xlf:';

    protected Src\Widget\Provider\ConsentChartDataProvider $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Build subject
        $connection = $this->getConnectionPool()->getConnectionForTable(Src\Domain\Model\Consent::TABLE_NAME);
        $this->subject = new Src\Widget\Provider\ConsentChartDataProvider($connection);

        // Import data
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tx_formconsent_domain_model_consent.csv');
    }

    #[Framework\Attributes\Test]
    public function getChartDataReturnsCorrectChartData(): void
    {
        $expectedApprovedCount = 1;
        $expectedNonApprovedCount = 2;
        $expectedDismissedCount = 1;
        $chartData = $this->subject->getChartData();

        $labels = $chartData['labels'];
        self::assertSame(self::$languagePrefix . 'charts.approved', $labels[0]);
        self::assertSame(self::$languagePrefix . 'charts.nonApproved', $labels[1]);
        self::assertSame(self::$languagePrefix . 'charts.dismissed', $labels[2]);

        $data = $chartData['datasets'][0]['data'];
        self::assertSame($expectedApprovedCount, $data[0]);
        self::assertSame($expectedNonApprovedCount, $data[1]);
        self::assertSame($expectedDismissedCount, $data[2]);
    }
}
