<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Domain\Repository;

use EliasHaeussler\Typo3FormConsent as Src;
use EliasHaeussler\Typo3FormConsent\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;

/**
 * ConsentRepositoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Repository\ConsentRepository::class)]
final class ConsentRepositoryTest extends Tests\Functional\ExtbaseRequestAwareFunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    protected Src\Domain\Repository\ConsentRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Build subject
        $this->subject = $this->getContainer()->get(Src\Domain\Repository\ConsentRepository::class);

        // Import data
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/tx_formconsent_domain_model_consent.csv');

        // @todo Remove once support for TYPO3 v12 is dropped
        if ((new Core\Information\Typo3Version())->getMajorVersion() < 13) {
            $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/tx_formconsent_domain_model_consent.v12.csv');
        } else {
            $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/Database/tx_formconsent_domain_model_consent.v13.csv');
        }
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('findByValidationHashReturnsValidConsentDataProvider')]
    public function findByValidationHashReturnsValidConsent(string $hash, int $expectedUid): void
    {
        $consent = $this->subject->findOneByValidationHash($hash);

        self::assertInstanceOf(Src\Domain\Model\Consent::class, $consent);
        self::assertSame($hash, $consent->getValidationHash());
        self::assertSame($expectedUid, $consent->getUid());
    }

    #[Framework\Attributes\Test]
    public function findByValidationHashDoesNotReturnDeletedConsent(): void
    {
        $queryResult = $this->subject->findOneByValidationHash('blub');
        self::assertNull($queryResult);
    }

    #[Framework\Attributes\Test]
    public function findByValidationHashDoesNotReturnExpiredConsent(): void
    {
        $queryResult = $this->subject->findOneByValidationHash('dummy');
        self::assertNull($queryResult);
    }

    #[Framework\Attributes\Test]
    public function findByValidationHashReturnsEmptyQueryResultIfConsentDoesNotExist(): void
    {
        $queryResult = $this->subject->findOneByValidationHash('some-invalid-hash');
        self::assertNull($queryResult);
    }

    /**
     * @return \Generator<string, array{string, int}>
     */
    public static function findByValidationHashReturnsValidConsentDataProvider(): \Generator
    {
        yield 'no expiry date' => ['foo', 1];
        yield 'valid until 2038' => ['baz', 2];
    }
}
