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
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Exception;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * FormValuesTypeTransformer
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class FormValuesTypeTransformer implements TypeTransformerInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return JsonType<string, mixed>
     * @throws \JsonException
     */
    public function transform(FormRuntime $formRuntime = null): JsonType
    {
        if (null === $formRuntime) {
            throw new \InvalidArgumentException('Expected a valid FormRuntime object, but gut none.', 1646044591);
        }

        // Early return if form state is not available
        $formState = $formRuntime->getFormState();
        if (null === $formState) {
            return JsonType::fromArray([]);
        }

        // Get all form values
        $formValues = $formState->getFormValues();

        // Remove honeypot field
        $honeypotIdentifier = $this->getHoneypotIdentifier($formRuntime);
        unset($formValues[$honeypotIdentifier]);

        foreach ($formValues as $key => $value) {
            if (\is_object($value)) {
                if ($value instanceof ExtbaseFileReference) {
                    $value = $value->getOriginalResource();
                }
                if ($value instanceof CoreFileReference) {
                    $formValues[$key] = $value->getOriginalFile()->getUid();
                }
            }
        }

        return JsonType::fromArray($formValues);
    }

    private function getHoneypotIdentifier(FormRuntime $formRuntime): ?string
    {
        // @todo This highly depends on internal logic in FormRuntime
        //       which is likely to be changed in the future. Consider
        //       refactoring this to a more robust solution or drop it
        //       completely to avoid inconsistencies in future versions.

        $formState = $formRuntime->getFormState();

        // Early return if form state is not available (this should never happen)
        if (null === $formState) {
            return null;
        }

        // Get last displayed page
        $lastDisplayedPageIndex = $formState->getLastDisplayedPageIndex();
        try {
            $currentPage = $formRuntime->getFormDefinition()->getPageByIndex($lastDisplayedPageIndex);
        } catch (Exception $e) {
            // If last displayed page is not set, try to use current page instead
            $currentPage = $formRuntime->getCurrentPage();
        }

        // Early return if neither last displayed page nor current page are available
        if ($currentPage === null) {
            return null;
        }

        // Build honeypot session identifier
        $frontendUser = $this->getTypoScriptFrontendController()->fe_user;
        $isUserAuthenticated = (bool)$this->context->getPropertyFromAspect('frontend.user', 'isLoggedIn', false);
        $sessionType = $isUserAuthenticated ? 'user' : 'ses';
        $honeypotSessionIdentifier = implode('', [
            FormRuntime::HONEYPOT_NAME_SESSION_IDENTIFIER,
            $formRuntime->getIdentifier(),
            $currentPage->getIdentifier(),
        ]);

        return (string)$frontendUser->getKey($sessionType, $honeypotSessionIdentifier) ?: null;
    }

    public static function getName(): string
    {
        return 'formValues';
    }

    private function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
