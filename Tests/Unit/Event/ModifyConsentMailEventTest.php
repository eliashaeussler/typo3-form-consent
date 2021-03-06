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

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Event;

use EliasHaeussler\Typo3FormConsent\Event\ModifyConsentMailEvent;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ModifyConsentMailEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ModifyConsentMailEventTest extends UnitTestCase
{
    use ProphecyTrait;

    protected ModifyConsentMailEvent $subject;
    protected FluidEmail $mail;
    protected FormRuntime $formRuntime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mail = $this->prophesize(FluidEmail::class)->reveal();
        $this->formRuntime = $this->prophesize(FormRuntime::class)->reveal();
        $this->subject = new ModifyConsentMailEvent($this->mail, $this->formRuntime);
    }

    /**
     * @test
     */
    public function getMailReturnsInitialMail(): void
    {
        $expected = $this->mail;
        self::assertSame($expected, $this->subject->getMail());
    }

    /**
     * @test
     */
    public function getFormRuntimeReturnsInitialFormRuntime(): void
    {
        $expected = $this->formRuntime;
        self::assertSame($expected, $this->subject->getFormRuntime());
    }
}
