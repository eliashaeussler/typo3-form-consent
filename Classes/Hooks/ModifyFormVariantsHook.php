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

namespace EliasHaeussler\Typo3FormConsent\Hooks;

use EliasHaeussler\Typo3FormConsent\Domain\Variants\ConsentVariantManager;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Event listener to create form variants
 */
#[Autoconfigure(public: true)]
class ModifyFormVariantsHook
{
    public function __construct(protected ConsentVariantManager $consentVariantManager){}

    /**
     * @param string $formPersistenceIdentifier
     * @param array $form
     * @return array
     */
    public function beforeFormCreate(string $formPersistenceIdentifier, array $form): array
    {
        return $this->createVariantsWithFinishers($form);
    }

    /**
     * @param string $formPersistenceIdentifier
     * @param array $form
     * @return array
     */
    public function beforeFormSave(string $formPersistenceIdentifier, array $form): array
    {
        return $this->createVariantsWithFinishers($form);
    }

    protected function createVariantsWithFinishers(array $form): array
    {
        // modify variants only if Consent finisher is selected
        if (!$this->consentVariantManager->formHasConsentFinisher($form)) {
            return $form;
        }

        $finishersThatNeedConsent = $this->consentVariantManager->findFinishersWithConsentNeeded($form);

        // always rebuild consent variants
        $form = $this->consentVariantManager->removeConsentVariant($form);

        if (!empty($finishersThatNeedConsent)) {
            $form = $this->consentVariantManager->removeFinishersWithConsentNeededFromDefault($form, $finishersThatNeedConsent);
            $consentVariant = [];
            $consentVariant['identifier'] = ConsentVariantManager::CONSENT_APPROVED_VARIANT_IDENTIFIER;
            $consentVariant['condition'] = ConsentVariantManager::CONSENT_APPROVED_VARIANT_CONDITION;
            $consentVariant['finishers'] = $finishersThatNeedConsent;
            $form['variants'][] = $consentVariant;
        }
        return $form;
    }
}
