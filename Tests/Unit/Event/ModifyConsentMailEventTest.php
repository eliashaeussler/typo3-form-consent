<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace EliasHaeussler\Typo3FormConsent\Tests\Unit\Event;

use EliasHaeussler\Typo3FormConsent\Event\ModifyConsentMailEvent;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ModifyConsentMailEventTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ModifyConsentMailEventTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var ModifyConsentMailEvent
     */
    protected $subject;

    /**
     * @var FluidEmail
     */
    protected $mail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mail = $this->prophesize(FluidEmail::class)->reveal();
        $this->subject = new ModifyConsentMailEvent($this->mail);
    }

    /**
     * @test
     */
    public function getMailReturnsInitialMail(): void
    {
        $expected = $this->mail;
        self::assertSame($expected, $this->subject->getMail());
    }
}
