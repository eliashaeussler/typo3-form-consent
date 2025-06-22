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

namespace EliasHaeussler\Typo3FormConsent\Domain\Variants;

class ConsentVariantManager
{
    private const CONSENT_VARIANT_IDENTIFIERS = [
        'approval' => '1265789653-post-consent-approval-variant',
        'dismissal' => '1265789653-post-consent-dismissal-variant'
    ];
    public const FINISHER_CONSENT_FLAGS = [
        'approval' => 'needsApproval',
        'dismissal' => 'needsDismissal'
    ];
    public const CONSENT_VARIANT_CONDITIONS = [
        'approval' => 'isConsentApproved()',
        'dismissal' => 'isConsentDismissed()'
    ];

    /**
     * Used before showing the form definition in editor
     * @param array $formDefinition
     * @return array
     */
    public function streamlineFormFinishers(array $formDefinition): array
    {
        $consentFinishers = $this->getConsentVariantFinishers($formDefinition);
        $formDefinition = $this->mergeFinishers(
            $formDefinition,
            $consentFinishers
        );
        return $this->removeConsentVariants($formDefinition);
    }

    /**
     * Used before saving the form definition
     * @param array $form
     * @return array
     */
    public function addConsentVariants(array $form = []): array
    {
        if (!$this->formHasConsentFinisher($form)) {
            return $form;
        }
        // reset
        $form = $this->removeConsentVariants($form);

        foreach (['approval', 'dismissal'] as $condition) {
            $finishersWithCondition = $this->findFinishersWithConsentCondition(
                $form,
                self::FINISHER_CONSENT_FLAGS[$condition]
            );
            $form = $this->createConsentVariant($form, $finishersWithCondition, $condition);
            $form = $this->removeFinishersWithConsentConditionFromDefault($form, $finishersWithCondition);
        }

        return $form;
    }

    protected function createConsentVariant(array $form, array $finishersWithApprovalCondition, string $condition): array
    {
        // exit if no finishers in variant
        if (empty($finishersWithApprovalCondition)) {
            return $form;
        }
        $form['variants'][] = [
            'identifier' => self::CONSENT_VARIANT_IDENTIFIERS[$condition],
            'condition' => self::CONSENT_VARIANT_CONDITIONS[$condition],
            'finishers' => $finishersWithApprovalCondition
        ];
        return $form;
    }

    /**
     * removes all consent relates form variants, used as reset before rebuilding the variants
     * @param array $form
     * @return array
     */
    protected function removeConsentVariants(array $form = []): array
    {
        foreach ($form['variants'] ?? [] as $key => $variant) {
            if (in_array(($variant['identifier'] ?? ''), self::CONSENT_VARIANT_IDENTIFIERS)) {
                unset($form['variants'][$key]);
            }
        }
        $form['variants'] = array_values($form['variants'] ?? []);
        if (empty($form['variants'])) {
            unset($form['variants']);
        }
        return $form;
    }

    /**
     * @param array $form
     * @return array
     */
    protected function getConsentVariantFinishers(array $form): array
    {
        $variantFinishers = [];
        foreach ($form['variants'] ?? [] as $variant) {
            if (in_array(($variant['identifier'] ?? ''), self::CONSENT_VARIANT_IDENTIFIERS)) {
                $variantFinishers = array_merge($variantFinishers, $variant['finishers'] ?? []);
            }
        }
        return $variantFinishers;
    }

    /**
     * Since variants are not shown in the form editor,
     * we combine the default finishers with the variants finishers
     * @param array $formDefinition
     * @param array $variantFinishers
     * @return array
     */
    protected function mergeFinishers(array $formDefinition, array $variantFinishers): array
    {
        $formDefinition['finishers'] = array_merge($formDefinition['finishers'] ?? [], $variantFinishers);
        return $formDefinition;
    }

    /**
     * @param array $form
     * @param $consentOptionValue
     * @return array
     */
    protected function findFinishersWithConsentCondition(array $form, $consentOptionValue): array
    {
        $finishersWithConsentCondition = [];
        foreach ((array)($form['finishers'] ?? []) as $finisher) {
            $options = (array)($finisher['options'] ?? []);
            if (($options['consentCondition'] ?? '') === $consentOptionValue) {
                $finishersWithConsentCondition[] = $finisher;
            }
        }
        return $finishersWithConsentCondition;
    }

    /**
     * @param array $form
     * @param $finishersWithConsentCondition
     * @return array
     */
    protected function removeFinishersWithConsentConditionFromDefault(array $form, $finishersWithConsentCondition): array
    {
        $form['finishers'] = array_values(array_udiff(
            $form['finishers'] ?? [],
            $finishersWithConsentCondition,
            function ($a, $b) {
                return $a <=> $b;
            }));
        return $form;
    }

    /**
     * @param array $form
     * @return bool
     */
    protected function formHasConsentFinisher(array $form): bool
    {
        foreach ($form['finishers'] ?? [] as $finisher) {
            if ($finisher['identifier'] ?? '' === 'Consent') {
                return true;
            }
        }
        return false;
    }
}
