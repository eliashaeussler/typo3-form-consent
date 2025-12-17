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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Compatibility\Migration;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;
use TYPO3\TestingFramework;

/**
 * HmacHashMigrationTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Compatibility\Migration\HmacHashMigration::class)]
final class HmacHashMigrationTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private Src\Compatibility\Migration\HmacHashMigration $subject;

    public function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'e52bf8903ee05ddfa65107175fbf7c72fd57482f28733701585f11c04885cab5474f2811c442ba87f93e1a63db8c0443';

        $this->subject = new Src\Compatibility\Migration\HmacHashMigration(new Core\Crypto\HashService());
    }

    #[Framework\Attributes\Test]
    public function migrateReturnsGivenStringIfNoMigrationIsNeeded(): void
    {
        $string = 'foobe86a0104bea86e3102d6931e120dd6a4566fb50';

        self::assertSame($string, $this->subject->migrate($string, Form\Security\HashScope::ResourcePointer));
    }

    #[Framework\Attributes\Test]
    public function migrateReturnsGivenStringIfStringDoesNotHaveHmacAppended(): void
    {
        self::assertSame(
            'foo',
            $this->subject->migrate('foo', Form\Security\HashScope::ResourcePointer),
        );
    }

    /**
     * @return \Generator<string, array{Extbase\Security\HashScope|Form\Security\HashScope, string}>
     */
    public static function migrateReturnsGivenStringWithMigratedHmacAppendedDataProvider(): \Generator
    {
        // @todo Remove once support for TYPO3 v13 is dropped
        $typo3Version = new Core\Information\Typo3Version();
        $v14 = $typo3Version->getMajorVersion() === 14;

        yield 'ReferringRequest' => [
            Extbase\Security\HashScope::ReferringRequest,
            $v14 ? 'foo5bbaa9729251609473cd14926e46030d4fdc04ed83a11ce8645b5ef88a0958a9' : 'fooeaa999e3f43a1a8e63c5fc399c570dd19468f7c6',
        ];
        yield 'ReferringArguments' => [
            Extbase\Security\HashScope::ReferringArguments,
            $v14 ? 'foo51a5ab03254d75c356bc74c7a611e324953acf36f1ab084b3c2e10742a21c0a1' : 'foo44104958c0c3fd4492cc488d52c047d768dac9f9',
        ];
        yield 'TrustedProperties' => [
            Extbase\Security\HashScope::TrustedProperties,
            $v14 ? 'foo36b72dcc980f4872cd47778ecdba8f0869e8077ac83616b393b323502fe599d6' : 'fooa7fea09ebf675eea58acdadb6cbf608cd25d8cd2',
        ];
        yield 'FormState' => [
            Form\Security\HashScope::FormState,
            $v14 ? 'foo2ec50719ebb99fa8203f56d727a3da8c81e17ff188526f3c5e749feab22a9740' : 'foobc178d95510a2bd1c46aea04b049bf089f848775',
        ];
        yield 'FormSession' => [
            Form\Security\HashScope::FormSession,
            $v14 ? 'foob5df4983e1ef1e3f13b9dad3051202230169e3f7da85d47b1767b41a881e9c66' : 'foo16b5167577eb96cd9b28bed88ccc2b7239ac5668',
        ];
        yield 'ResourcePointer' => [
            Form\Security\HashScope::ResourcePointer,
            'foobe86a0104bea86e3102d6931e120dd6a4566fb50',
        ];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('migrateReturnsGivenStringWithMigratedHmacAppendedDataProvider')]
    public function migrateReturnsGivenStringWithMigratedHmacAppended(
        Extbase\Security\HashScope|Form\Security\HashScope $hashScope,
        string $expected,
    ): void {
        // HMAC generated with TYPO3 v12
        $string = 'foo4581597e40df301d174185e6bca346b412c1953c';

        self::assertSame($expected, $this->subject->migrate($string, $hashScope));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);

        parent::tearDown();
    }
}
