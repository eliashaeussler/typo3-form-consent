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

        // @todo Remove once support for TYPO3 v12 is dropped
        if ((new Core\Information\Typo3Version())->getMajorVersion() < 13) {
            self::markTestSkipped('Test can be executed on TYPO3 >= 13 only.');
        }

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'e52bf8903ee05ddfa65107175fbf7c72fd57482f28733701585f11c04885cab5474f2811c442ba87f93e1a63db8c0443';

        $this->subject = new Src\Compatibility\Migration\HmacHashMigration();
    }

    #[Framework\Attributes\Test]
    public function migrateReturnsGivenStringIfNoMigrationIsNeeded(): void
    {
        $string = 'foo313c270e69b0eea4749c7decd66120c50a579586';

        self::assertSame($string, $this->subject->migrate($string, 'foo'));
    }

    #[Framework\Attributes\Test]
    public function migrateReturnsGivenStringIfStringDoesNotHaveHmacAppended(): void
    {
        self::assertSame('foo', $this->subject->migrate('foo', 'foo'));
    }

    #[Framework\Attributes\Test]
    public function migrateReturnsGivenStringWithMigratedHmacAppended(): void
    {
        // HMAC generated with TYPO3 v12
        $string = 'foo4581597e40df301d174185e6bca346b412c1953c';

        // HMAC generated with TYPO3 v13
        $expected = 'foo313c270e69b0eea4749c7decd66120c50a579586';

        self::assertSame($expected, $this->subject->migrate($string, 'foo'));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);

        parent::tearDown();
    }
}
