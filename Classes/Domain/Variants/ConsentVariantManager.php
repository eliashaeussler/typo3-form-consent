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

final class ConsentVariantManager
{

    public const CONSENT_APPROVED_VARIANT_IDENTIFIER = '1265789653-post-consent-approval-variant';
    public const CONSENT_APPROVED_VARIANT_CONDITION = 'isConsentApproved()';

    public function streamlineFormFinishers(array $formDefinition): array
    {
        $consentFinishers = $this->getConsentVariantFinishers($formDefinition);
        $formDefinition =  $this->mergeFinishers(
            $formDefinition,
            $consentFinishers
        );
        return $this->removeConsentVariant($formDefinition);
    }

    /**
     * We always reset the consent variant
     * @param array $form
     * @return array
     */
    public function removeConsentVariant(array $form): array
    {
        foreach ($form['variants'] ?? [] as $key => $variant) {
            if (($variant['identifier'] ?? '') === self::CONSENT_APPROVED_VARIANT_IDENTIFIER) {
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
     * We always reset the consent variant
     * @param array $form
     * @return array
     */
    public function getConsentVariantFinishers(array $form): array
    {
        foreach ($form['variants'] ?? [] as $variant) {
            if (($variant['identifier'] ?? '') === self::CONSENT_APPROVED_VARIANT_IDENTIFIER) {
                return $variant['finishers'] ?? [];
            }
        }
        return [];
    }

    /**
     * @param array $formDefinition
     * @param array $variantFinishers
     * @return array
     */
    public function mergeFinishers(array $formDefinition, array $variantFinishers): array
    {
        $formDefinition['finishers'] = array_merge($formDefinition['finishers'] ?? [], $variantFinishers);
        return $formDefinition;
    }

    /**
     * @param array $form
     * @return array
     */
    public function findFinishersWithConsentNeeded(array $form): array
    {
        $finishersThatNeedConsent = [];
        foreach ((array)($form['finishers'] ?? []) as $finisher) {
            $options = (array)($finisher['options'] ?? []);
            if ($options['needsConsent'] ?? false) {
                $finishersThatNeedConsent[] = $finisher;
            }
        }
        return $finishersThatNeedConsent;
    }

    /**
     * @param array $form
     * @param $finishersWithConsentNeeded
     * @return array
     */
    public function removeFinishersWithConsentNeededFromDefault(array $form, $finishersWithConsentNeeded): array
    {
        $form['finishers'] = array_values(array_udiff(
            $form['finishers'] ?? [],
            $finishersWithConsentNeeded,
            function ($a, $b) {
                return $a <=> $b;
            }));
        return $form;
    }

    /**
     * @param array $form
     * @return bool
     */
    public function formHasConsentFinisher(array $form): bool
    {
        foreach ($form['finishers'] ?? [] as $finisher) {
            if ($finisher['identifier'] ?? '' === 'Consent') {
                return true;
            }
        }
        return false;
    }
}
