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

namespace EliasHaeussler\Typo3FormConsent\Event;

use EliasHaeussler\Typo3FormConsent\Domain;
use TYPO3\CMS\Form;

/**
 * ModifyConsentEvent
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ModifyConsentEvent
{
    public function __construct(
        private readonly Domain\Model\Consent $consent,
        private readonly Form\Domain\Finishers\FinisherContext $finisherContext,
    ) {}

    public function getConsent(): Domain\Model\Consent
    {
        return $this->consent;
    }

    public function getFinisherContext(): Form\Domain\Finishers\FinisherContext
    {
        return $this->finisherContext;
    }
}
