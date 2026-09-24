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

namespace EliasHaeussler\Typo3FormConsent\Form;

use EliasHaeussler\Typo3FormConsent\Enums;

/**
 * ConsentVariantResolver
 *
 * Derives form variants from finishers that are marked as consent dependent in
 * the form editor. Marked finishers are moved into a generated variant, since
 * variants replace the whole finisher list once their condition matches.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ConsentVariantResolver
{
    private const CONSENT_FINISHER_IDENTIFIER = 'Consent';

    /**
     * @param array<string, mixed> $formDefinition
     * @return array<string, mixed>
     */
    public function resolve(array $formDefinition): array
    {
        $finishers = $formDefinition['finishers'] ?? null;

        if (!is_array($finishers) || !$this->containsConsentFinisher($finishers)) {
            return $formDefinition;
        }

        $defaultFinishers = [];
        $consentDependentFinishers = [];

        foreach ($finishers as $finisher) {
            $condition = $this->resolveCondition($finisher);

            if ($condition === null) {
                $defaultFinishers[] = $finisher;
            } else {
                $consentDependentFinishers[$condition->value][] = $finisher;
            }
        }

        if ($consentDependentFinishers === []) {
            return $formDefinition;
        }

        $existingVariants = $formDefinition['variants'] ?? [];

        $formDefinition['finishers'] = $defaultFinishers;
        $formDefinition['variants'] = [
            // Generated variants are prepended to allow manually configured
            // variants to overrule them
            ...$this->buildVariants($consentDependentFinishers),
            ...(is_array($existingVariants) ? array_values($existingVariants) : []),
        ];

        return $formDefinition;
    }

    /**
     * @param array<string, list<array<string, mixed>>> $consentDependentFinishers
     * @return list<array{identifier: string, condition: string, finishers: list<array<string, mixed>>}>
     */
    private function buildVariants(array $consentDependentFinishers): array
    {
        $variants = [];

        foreach (Enums\ConsentCondition::cases() as $condition) {
            $finishers = $consentDependentFinishers[$condition->value] ?? [];

            if ($finishers === []) {
                continue;
            }

            $variants[] = [
                'identifier' => $condition->variantIdentifier(),
                'condition' => $condition->condition(),
                'finishers' => $finishers,
            ];
        }

        return $variants;
    }

    private function resolveCondition(mixed $finisher): ?Enums\ConsentCondition
    {
        // The Consent finisher triggers the conditions and must never be
        // moved into a variant itself
        if (!is_array($finisher) || $this->isConsentFinisher($finisher)) {
            return null;
        }

        $options = $finisher['options'] ?? null;

        if (!is_array($options) || !is_string($options[Enums\ConsentCondition::FINISHER_OPTION] ?? null)) {
            return null;
        }

        return Enums\ConsentCondition::tryFrom($options[Enums\ConsentCondition::FINISHER_OPTION]);
    }

    /**
     * @param array<mixed> $finishers
     */
    private function containsConsentFinisher(array $finishers): bool
    {
        foreach ($finishers as $finisher) {
            if (is_array($finisher) && $this->isConsentFinisher($finisher)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $finisher
     */
    private function isConsentFinisher(array $finisher): bool
    {
        return ($finisher['identifier'] ?? null) === self::CONSENT_FINISHER_IDENTIFIER;
    }
}
