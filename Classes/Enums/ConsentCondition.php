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

namespace EliasHaeussler\Typo3FormConsent\Enums;

/**
 * ConsentCondition
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
enum ConsentCondition: string
{
    case Approval = 'needsApproval';
    case Dismissal = 'needsDismissal';

    /**
     * Name of the finisher option this condition is configured with.
     */
    public const FINISHER_OPTION = 'consentCondition';

    /**
     * Expression language condition, @see ConsentConditionFunctionsProvider.
     */
    public function condition(): string
    {
        return match ($this) {
            self::Approval => 'isConsentApproved()',
            self::Dismissal => 'isConsentDismissed()',
        };
    }

    public function variantIdentifier(): string
    {
        return match ($this) {
            self::Approval => 'form-consent-approval-variant',
            self::Dismissal => 'form-consent-dismissal-variant',
        };
    }
}
