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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Event;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * ApproveConsentEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Event\ApproveConsentEvent::class)]
final class ApproveConsentEventTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Event\ApproveConsentEvent $subject;
    protected Src\Domain\Model\Consent $consent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consent = new Src\Domain\Model\Consent();
        $this->subject = new Src\Event\ApproveConsentEvent($this->consent);
    }

    #[Framework\Attributes\Test]
    public function getConsentReturnsInitialConsent(): void
    {
        $expected = $this->consent;
        self::assertSame($expected, $this->subject->getConsent());
    }

    #[Framework\Attributes\Test]
    public function getResponseReturnsResponse(): void
    {
        self::assertNull($this->subject->getResponse());

        $response = new Core\Http\Response();

        self::assertSame($response, $this->subject->setResponse($response)->getResponse());
    }
}
