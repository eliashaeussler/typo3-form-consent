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
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * FormRequestTypeTransformer
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class FormRequestTypeTransformer implements TypeTransformerInterface
{
    /**
     * @var HashService
     */
    private $hashService;

    public function __construct(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @return JsonType<string, array<string, array<string, mixed>>>
     * @throws \JsonException
     */
    public function transform(FormRuntime $formRuntime = null): JsonType
    {
        if (null === $formRuntime) {
            throw new \InvalidArgumentException('Expected a valid FormRuntime object, but gut none.', 1646044629);
        }

        // Handle submitted form values
        $requestParameters = [];
        if (\is_array($formRuntime->getRequest()->getParsedBody())) {
            $requestParameters = $formRuntime->getRequest()->getParsedBody();
        }

        // Handle uploaded files
        $uploadedFiles = $formRuntime->getRequest()->getUploadedFiles();
        array_walk_recursive($uploadedFiles, function (&$value, string $elementIdentifier) use ($formRuntime): void {
            $file = $formRuntime[$elementIdentifier];
            if ($file instanceof ExtbaseFileReference || $file instanceof CoreFileReference) {
                $value = $this->transformUploadedFile($file);
            }
        });

        ArrayUtility::mergeRecursiveWithOverrule($requestParameters, $uploadedFiles);

        return JsonType::fromArray($requestParameters);
    }

    /**
     * @param CoreFileReference|ExtbaseFileReference $file
     * @return array{submittedFile: array{resourcePointer: string}}
     */
    private function transformUploadedFile($file): array
    {
        if ($file instanceof ExtbaseFileReference) {
            $file = $file->getOriginalResource();
        }
        if ($file instanceof CoreFileReference) {
            $file = $file->getOriginalFile();
        }

        return [
            'submittedFile' => [
                'resourcePointer' => $this->hashService->appendHmac('file:' . $file->getUid()),
            ],
        ];
    }

    public static function getName(): string
    {
        return 'formRequest';
    }
}
