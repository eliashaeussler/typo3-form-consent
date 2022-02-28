<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Registry\Dto;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Registry\Dto\ConsentState;
use EliasHaeussler\Typo3FormConsent\Type\JsonType;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ConsentStateTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConsentStateTest extends UnitTestCase
{
    protected Consent $consent;
    protected ConsentState $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consent = new Consent();
        $this->subject = new ConsentState($this->consent);
    }

    /**
     * @test
     */
    public function isApprovedReturnsConsentApprovalState(): void
    {
        self::assertFalse($this->subject->isApproved());

        $this->consent->setApproved(true);

        self::assertTrue($this->subject->isApproved());
    }

    /**
     * @test
     */
    public function isDismissedReturnsConsentDismissalState(): void
    {
        $this->consent->setApproved(true);

        self::assertFalse($this->subject->isDismissed());

        $this->consent->setOriginalRequestParameters(JsonType::fromArray([]));
        $this->consent->setApproved(false);

        self::assertFalse($this->subject->isDismissed());

        $this->consent->setOriginalRequestParameters(null);

        self::assertTrue($this->subject->isDismissed());
    }
}
