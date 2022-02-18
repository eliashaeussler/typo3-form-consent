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

namespace EliasHaeussler\Typo3FormConsent\Event;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;

/**
 * DismissConsentEvent
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class DismissConsentEvent
{
    /**
     * @var Consent
     */
    protected $consent;

    public function __construct(Consent $consent)
    {
        $this->consent = $consent;
    }

    public function getConsent(): Consent
    {
        return $this->consent;
    }
}
