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

namespace EliasHaeussler\Typo3FormConsent\ExpressionLanguage\FunctionsProvider;

use EliasHaeussler\Typo3FormConsent\Registry;
use Symfony\Component\ExpressionLanguage;
use TYPO3\CMS\Form;

/**
 * ConsentConditionFunctionsProvider
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentConditionFunctionsProvider implements ExpressionLanguage\ExpressionFunctionProviderInterface
{
    /**
     * @return list<ExpressionLanguage\ExpressionFunction>
     */
    public function getFunctions(): array
    {
        return [
            $this->getIsConsentApprovedFunction(),
            $this->getIsConsentDismissedFunction(),
        ];
    }

    private function getIsConsentApprovedFunction(): ExpressionLanguage\ExpressionFunction
    {
        return new ExpressionLanguage\ExpressionFunction('isConsentApproved', static function (): void {
            // Not implemented, we only use the evaluator
        }, static function ($arguments): bool {
            $formRuntime = $arguments['formRuntime'] ?? null;

            if (!($formRuntime instanceof Form\Domain\Runtime\FormRuntime)) {
                return false;
            }

            return Registry\ConsentManagerRegistry::isConsentApproved($formRuntime->getFormDefinition()->getPersistenceIdentifier());
        });
    }

    private function getIsConsentDismissedFunction(): ExpressionLanguage\ExpressionFunction
    {
        return new ExpressionLanguage\ExpressionFunction('isConsentDismissed', static function (): void {
            // Not implemented, we only use the evaluator
        }, static function ($arguments): bool {
            $formRuntime = $arguments['formRuntime'] ?? null;

            if (!($formRuntime instanceof Form\Domain\Runtime\FormRuntime)) {
                return false;
            }

            return Registry\ConsentManagerRegistry::isConsentDismissed($formRuntime->getFormDefinition()->getPersistenceIdentifier());
        });
    }
}
