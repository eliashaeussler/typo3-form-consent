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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Domain\Repository;

use Doctrine\DBAL\DBALException;
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Domain\Repository\ConsentRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\TestingFramework\Core\Exception;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * ConsentRepositoryTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConsentRepositoryTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = [
        'form',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/form_consent',
    ];

    /**
     * @var ConsentRepository
     */
    protected $subject;

    /**
     * @throws DBALException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Build subject
        $this->subject = new ConsentRepository(GeneralUtility::makeInstance(ObjectManager::class));
        $this->subject->injectPersistenceManager(GeneralUtility::makeInstance(PersistenceManager::class));
        $this->subject->initializeObject();

        // Import data
        $this->importDataSet(__DIR__ . '/../../Fixtures/tx_formconsent_domain_model_consent.xml');
    }

    /**
     * @test
     * @dataProvider findByValidationHashReturnsValidConsentDataProvider
     * @param string $hash
     * @param int $expectedUid
     */
    public function findByValidationHashReturnsValidConsent(string $hash, int $expectedUid): void
    {
        /** @var Consent $consent */
        $consent = $this->subject->findOneByValidationHash($hash);
        self::assertInstanceOf(Consent::class, $consent);
        self::assertEquals($hash, $consent->getValidationHash());
        self::assertEquals($expectedUid, $consent->getUid());
    }

    /**
     * @test
     */
    public function findByValidationHashDoesNotReturnDeletedConsent(): void
    {
        $queryResult = $this->subject->findOneByValidationHash('blub');
        self::assertNull($queryResult);
    }

    /**
     * @test
     */
    public function findByValidationHashDoesNotReturnExpiredConsent(): void
    {
        $queryResult = $this->subject->findOneByValidationHash('dummy');
        self::assertNull($queryResult);
    }

    /**
     * @test
     */
    public function findByValidationHashReturnsEmptyQueryResultIfConsentDoesNotExist(): void
    {
        $queryResult = $this->subject->findOneByValidationHash('some-invalid-hash');
        self::assertNull($queryResult);
    }

    /**
     * @return \Generator<string, array>
     */
    public function findByValidationHashReturnsValidConsentDataProvider(): \Generator
    {
        yield 'no expiry date' => ['foo', 1];
        yield 'valid until 2038' => ['baz', 2];
    }
}
