<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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
use TYPO3\CMS\Form;
use TYPO3\TestingFramework;

/**
 * ModifyConsentMailEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Event\ModifyConsentMailEvent::class)]
final class ModifyConsentMailEventTest extends TestingFramework\Core\Unit\UnitTestCase
{
    protected Src\Event\ModifyConsentMailEvent $subject;
    protected Core\Mail\FluidEmail $mailStub;
    protected Form\Domain\Runtime\FormRuntime $formRuntimeStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailStub = self::createStub(Core\Mail\FluidEmail::class);
        $this->formRuntimeStub = self::createStub(Form\Domain\Runtime\FormRuntime::class);
        $this->subject = new Src\Event\ModifyConsentMailEvent($this->mailStub, $this->formRuntimeStub);
    }

    #[Framework\Attributes\Test]
    public function getMailReturnsInitialMail(): void
    {
        $expected = $this->mailStub;
        self::assertSame($expected, $this->subject->getMail());
    }

    #[Framework\Attributes\Test]
    public function getFormRuntimeReturnsInitialFormRuntime(): void
    {
        $expected = $this->formRuntimeStub;
        self::assertSame($expected, $this->subject->getFormRuntime());
    }
}
