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

namespace EliasHaeussler\Typo3FormConsent\Event\Listener;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Event\ApproveConsentEvent;
use EliasHaeussler\Typo3FormConsent\Type\JsonType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * InvokeFinishersOnConsentApprovalListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class InvokeFinishersOnConsentApprovalListener
{
    private FormPersistenceManagerInterface $formPersistenceManager;
    private PageRepository $pageRepository;

    public function __construct(FormPersistenceManagerInterface $formPersistenceManager, PageRepository $pageRepository)
    {
        $this->formPersistenceManager = $formPersistenceManager;
        $this->pageRepository = $pageRepository;
    }

    public function __invoke(ApproveConsentEvent $event): void
    {
        $consent = $event->getConsent();

        // Early return if original request is missing
        // or no finisher variants are configured
        if (
            empty($consent->getOriginalRequestParameters())
            || 0 === $consent->getOriginalContentElementUid()
            || !$this->arePostApprovalVariantsConfigured($consent->getFormPersistenceIdentifier())
        ) {
            return;
        }

        // Re-render form to invoke finishers
        $request = $this->createRequestFromOriginalRequestParameters($consent->getOriginalRequestParameters());
        $response = $this->dispatchFormReRendering($consent, $request);
        $event->setResponse($response);
    }

    private function dispatchFormReRendering(Consent $consent, ServerRequestInterface $serverRequest): ?ResponseInterface
    {
        // Fetch record of original content element
        $contentElementRecord = $this->fetchOriginalContentElementRecord($consent->getOriginalContentElementUid());

        // Early return if content element record cannot be resolved
        if (!\is_array($contentElementRecord)) {
            return null;
        }

        // Build extbase bootstrap object
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->start($contentElementRecord, 'tt_content', $serverRequest);
        $contentObjectRenderer->setUserObjectType(ContentObjectRenderer::OBJECTTYPE_USER_INT);
        $bootstrap = GeneralUtility::makeInstance(Bootstrap::class);
        $bootstrap->setContentObjectRenderer($contentObjectRenderer);

        $configuration = [
            'extensionName' => 'Form',
            'pluginName' => 'Formframework',
        ];

        try {
            // Dispatch extbase request
            $content = $bootstrap->run('', $configuration, $serverRequest);
            $response = new Response();
            $response->getBody()->write($content);

            return $response;
        } catch (ImmediateResponseException $exception) {
            // If any immediate response is thrown, use this for further processing
            return $exception->getResponse();
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchOriginalContentElementRecord(int $contentElementUid): ?array
    {
        // Early return if content element UID cannot be  determined
        if (0 === $contentElementUid) {
            return null;
        }

        // Fetch content element record
        $record = $this->pageRepository->checkRecord('tt_content', $contentElementUid);

        // Early return if content element record cannot be resolved
        if (!\is_array($record)) {
            return null;
        }

        return $this->pageRepository->getLanguageOverlay('tt_content', $record);
    }

    /**
     * @param JsonType<string, array<string, array<string, mixed>>> $originalRequestParameters
     */
    private function createRequestFromOriginalRequestParameters(JsonType $originalRequestParameters): ServerRequestInterface
    {
        return $this->getServerRequest()
            ->withMethod('POST')
            ->withParsedBody($originalRequestParameters->toArray());
    }

    private function arePostApprovalVariantsConfigured(string $formPersistenceIdentifier): bool
    {
        $formConfiguration = $this->formPersistenceManager->load($formPersistenceIdentifier);

        foreach ($formConfiguration['variants'] ?? [] as $variant) {
            if (str_contains($variant['condition'] ?? '', 'isConsentApproved()') && isset($variant['finishers'])) {
                return true;
            }
        }

        return false;
    }

    private function getServerRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
    }
}
