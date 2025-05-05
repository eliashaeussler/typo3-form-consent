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

namespace EliasHaeussler\Typo3FormConsent\Middleware;

use EliasHaeussler\Typo3FormConsent\Domain\Variants\ConsentVariantManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Http\Stream;

/**
 * Modify Form Editor output.
 */
class FormVariantMiddleware implements MiddlewareInterface
{
    public function __construct(protected ConsentVariantManager $consentVariantManager){}

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$this->requestIsInFormEditor($request)) {
            return $response;
        }
        $body = $response->getBody();
        $body->rewind();
        $content = $response->getBody()->getContents();
        $contentArray = json_decode($content, true);
        $formDefinition = $contentArray['formDefinition'] ?? [];
        if (!empty($formDefinition)) {
            $formDefinition = $this->consentVariantManager->streamlineFormFinishers($formDefinition);
            $contentArray['formDefinition'] = $formDefinition;
            $newContent = json_encode($contentArray);
            $body = new Stream('php://temp', 'rw');
            $body->write($newContent);
            return $response->withBody($body);
        }
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function requestIsInFormEditor(ServerRequestInterface $request): bool
    {
        if (!ApplicationType::fromRequest($request)->isBackend()) {
            return false;
        }
        return $request->getUri()->getPath() === '/typo3/module/manage/forms/FormEditor/saveForm';
    }
}
