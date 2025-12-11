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

use EliasHaeussler\Typo3FormConsent\Type;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;

/**
 * FormRequestTypeTransformer
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class FormRequestTypeTransformer implements TypeTransformer
{
    public function __construct(
        private Core\Crypto\HashService $hashService,
    ) {}

    /**
     * @return Type\JsonType<string, array<string, array<string, mixed>>>
     * @throws \JsonException
     */
    public function transform(Form\Domain\Runtime\FormRuntime $formRuntime): Type\JsonType
    {
        $request = $formRuntime->getRequest();
        $pluginNamespace = strtolower('tx_' . $request->getControllerExtensionName() . '_' . $request->getPluginName());

        // Handle submitted form values
        $requestParameters = [];
        if (\is_array($request->getParsedBody())) {
            $requestParameters = $request->getParsedBody();
        }

        // Handle uploaded files
        $uploadedFiles = $request->getUploadedFiles();
        array_walk_recursive($uploadedFiles, function (&$value, string $elementIdentifier) use ($formRuntime): void {
            $file = $formRuntime[$elementIdentifier];
            if ($file instanceof Extbase\Domain\Model\FileReference || $file instanceof Core\Resource\FileReference) {
                $value = $this->transformUploadedFile($file);
            }
        });

        // Prepend plugin namespace to uploaded files array if not exists
        // This is necessary since TYPO3 12.0, see https://github.com/typo3/typo3/commit/02198ea257b9f03f910b3b120392ab63fe792a8b
        if (!isset($uploadedFiles[$pluginNamespace])) {
            $uploadedFiles = [
                $pluginNamespace => $uploadedFiles,
            ];
        }

        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($requestParameters, $uploadedFiles);

        return Type\JsonType::fromArray($requestParameters);
    }

    /**
     * @return array{submittedFile: array{resourcePointer: string}}
     */
    private function transformUploadedFile(Core\Resource\FileReference|Extbase\Domain\Model\FileReference $file): array
    {
        if ($file instanceof Extbase\Domain\Model\FileReference) {
            $file = $file->getOriginalResource();
        }

        $file = $file->getOriginalFile();
        $resourcePointer = 'file:' . $file->getUid();

        return [
            'submittedFile' => [
                'resourcePointer' => $this->hashService->appendHmac(
                    $resourcePointer,
                    /* @phpstan-ignore argument.type */
                    Form\Security\HashScope::ResourcePointer->prefix(),
                ),
            ],
        ];
    }
}
