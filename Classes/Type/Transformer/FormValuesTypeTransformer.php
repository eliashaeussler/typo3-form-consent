<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\Type\Transformer;

use EliasHaeussler\Typo3FormConsent\Type\JsonType;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * FormValuesTypeTransformer
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class FormValuesTypeTransformer implements TypeTransformerInterface
{
    /**
     * @return JsonType<string, mixed>
     * @throws \JsonException
     */
    public function transform(FormRuntime $formRuntime = null): JsonType
    {
        if (null === $formRuntime) {
            throw new \InvalidArgumentException('Expected a valid FormRuntime object, NULL given.', 1646044591);
        }

        // Early return if form state is not available
        $formState = $formRuntime->getFormState();
        if (null === $formState) {
            return JsonType::fromArray([]);
        }

        // Get all form values
        $formValues = $formState->getFormValues();

        foreach ($formValues as $elementIdentifier => $value) {
            // Remove honeypot field
            if ($this->isHoneypotElement($elementIdentifier, $formRuntime)) {
                unset($formValues[$elementIdentifier]);
                continue;
            }

            // Resolve file references
            if ($value instanceof ExtbaseFileReference) {
                $value = $value->getOriginalResource();
            }
            if ($value instanceof CoreFileReference) {
                $formValues[$elementIdentifier] = $value->getOriginalFile()->getUid();
            }
        }

        return JsonType::fromArray($formValues);
    }

    private function isHoneypotElement(string $elementIdentifier, FormRuntime $formRuntime): bool
    {
        $element = $formRuntime->getFormDefinition()->getElementByIdentifier($elementIdentifier);

        return null !== $element && $element->getType() === 'Honeypot';
    }

    public static function getName(): string
    {
        return 'formValues';
    }
}
