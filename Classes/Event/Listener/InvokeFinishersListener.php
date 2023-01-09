<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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
use EliasHaeussler\Typo3FormConsent\Event\DismissConsentEvent;
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
 * InvokeFinishersListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class InvokeFinishersListener
{
    private FormPersistenceManagerInterface $formPersistenceManager;
    private PageRepository $pageRepository;

    public function __construct(FormPersistenceManagerInterface $formPersistenceManager, PageRepository $pageRepository)
    {
        $this->formPersistenceManager = $formPersistenceManager;
        $this->pageRepository = $pageRepository;
    }

    public function onConsentApprove(ApproveConsentEvent $event): void
    {
        $response = $this->invokeFinishers($event->getConsent(), 'isConsentApproved()');
        $event->setResponse($response);
    }

    public function onConsentDismiss(DismissConsentEvent $event): void
    {
        $response = $this->invokeFinishers($event->getConsent(), 'isConsentDismissed()');
        $event->setResponse($response);
    }

    private function invokeFinishers(Consent $consent, string $condition): ?ResponseInterface
    {
        // Early return if original request is missing
        // or no finisher variants are configured
        if (
            empty($consent->getOriginalRequestParameters())
            || $consent->getOriginalContentElementUid() === 0
            || !$this->areFinisherVariantsConfigured($consent->getFormPersistenceIdentifier(), $condition)
        ) {
            return null;
        }

        // Re-render form to invoke finishers
        $request = $this->createRequestFromOriginalRequestParameters($consent->getOriginalRequestParameters());

        return $this->dispatchFormReRendering($consent, $request);
    }

    private function dispatchFormReRendering(Consent $consent, ServerRequestInterface $serverRequest): ?ResponseInterface
    {
        // Fetch record of original content element
        $contentElementRecord = $this->fetchOriginalContentElementRecord($consent->getOriginalContentElementUid());

        // Early return if content element record cannot be resolved
        if (!\is_array($contentElementRecord)) {
            return null;
        }

        // Backup original server request object
        // @todo Remove once v10 support is dropped
        $originalRequest = $GLOBALS['TYPO3_REQUEST'];
        $GLOBALS['TYPO3_REQUEST'] = $serverRequest;

        // Build extbase bootstrap object
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        // @todo Enable third parameter once v10 support is dropped
        $contentObjectRenderer->start($contentElementRecord, 'tt_content'/**, $serverRequest */);
        $contentObjectRenderer->setUserObjectType(ContentObjectRenderer::OBJECTTYPE_USER_INT);
        $bootstrap = GeneralUtility::makeInstance(Bootstrap::class);

        if (method_exists($bootstrap, 'setContentObjectRenderer')) {
            $bootstrap->setContentObjectRenderer($contentObjectRenderer);
        } else {
            // @todo Remove once v10 support is dropped
            /* @phpstan-ignore-next-line */
            $bootstrap->cObj = $contentObjectRenderer;
        }

        $configuration = [
            'extensionName' => 'Form',
            'pluginName' => 'Formframework',
        ];

        try {
            // Dispatch extbase request
            // @todo Enable third parameter once v10 support is dropped
            $content = $bootstrap->run('', $configuration/**, $serverRequest */);
            $response = new Response();
            $response->getBody()->write($content);

            return $response;
        } catch (ImmediateResponseException $exception) {
            // If any immediate response is thrown, use this for further processing
            return $exception->getResponse();
        } finally {
            // Restore original server request object
            $GLOBALS['TYPO3_REQUEST'] = $originalRequest;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchOriginalContentElementRecord(int $contentElementUid): ?array
    {
        // Early return if content element UID cannot be  determined
        if ($contentElementUid === 0) {
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

    private function areFinisherVariantsConfigured(string $formPersistenceIdentifier, string $condition): bool
    {
        $formConfiguration = $this->formPersistenceManager->load($formPersistenceIdentifier);

        foreach ($formConfiguration['variants'] ?? [] as $variant) {
            if (str_contains($variant['condition'] ?? '', $condition) && isset($variant['finishers'])) {
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
