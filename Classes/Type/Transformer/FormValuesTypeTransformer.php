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

namespace EliasHaeussler\Typo3FormConsent\Type\Transformer;

use EliasHaeussler\Typo3FormConsent\Configuration;
use EliasHaeussler\Typo3FormConsent\Type;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;

/**
 * FormValuesTypeTransformer
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class FormValuesTypeTransformer implements TypeTransformer
{
    public function __construct(
        private readonly Configuration\Configuration $configuration,
    ) {}

    /**
     * @return Type\JsonType<string, mixed>
     * @throws \JsonException
     */
    public function transform(Form\Domain\Runtime\FormRuntime $formRuntime): Type\JsonType
    {
        // Early return if form state is not available
        $formState = $formRuntime->getFormState();
        if ($formState === null) {
            return Type\JsonType::fromArray([]);
        }

        // Get all form values
        $formValues = $formState->getFormValues();

        foreach ($formValues as $elementIdentifier => $value) {
            // Remove excluded elements
            if ($this->isElementExcluded($elementIdentifier, $formRuntime)) {
                unset($formValues[$elementIdentifier]);
                continue;
            }

            // Resolve file references
            if ($value instanceof Extbase\Domain\Model\FileReference) {
                $value = $value->getOriginalResource();
            }
            if ($value instanceof Core\Resource\FileReference) {
                $formValues[$elementIdentifier] = $value->getOriginalFile()->getUid();
            }
        }

        return Type\JsonType::fromArray($formValues);
    }

    private function isElementExcluded(string $elementIdentifier, Form\Domain\Runtime\FormRuntime $formRuntime): bool
    {
        $excludedElements = $this->configuration->getExcludedElementsFromPersistence();
        $element = $formRuntime->getFormDefinition()->getElementByIdentifier($elementIdentifier);

        return $element !== null && \in_array($element->getType(), $excludedElements, true);
    }
}
