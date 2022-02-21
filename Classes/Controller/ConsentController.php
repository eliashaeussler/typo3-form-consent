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

namespace EliasHaeussler\Typo3FormConsent\Controller;

use EliasHaeussler\Typo3FormConsent\Domain\Repository\ConsentRepository;
use EliasHaeussler\Typo3FormConsent\Event\ApproveConsentEvent;
use EliasHaeussler\Typo3FormConsent\Event\DismissConsentEvent;
use EliasHaeussler\Typo3FormConsent\Http\StringableResponseFactory;
use EliasHaeussler\Typo3FormConsent\Registry\ConsentManagerRegistry;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * ConsentController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConsentController extends ActionController
{
    /**
     * @var ConsentRepository
     */
    protected $consentRepository;

    /**
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var StringableResponseFactory
     */
    protected $stringableResponseFactory;

    public function __construct(
        ConsentRepository $consentRepository,
        PersistenceManagerInterface $persistenceManager,
        StringableResponseFactory $stringableResponseFactory
    ) {
        $this->consentRepository = $consentRepository;
        $this->persistenceManager = $persistenceManager;
        $this->stringableResponseFactory = $stringableResponseFactory;
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function approveAction(string $hash, string $email): ResponseInterface
    {
        $consent = $this->consentRepository->findOneByValidationHash($hash);

        // Add template variable
        $this->view->assign('consent', $consent);

        // Early return if consent could not be found
        if (null === $consent) {
            return $this->createErrorResponse('invalidConsent');
        }

        // Early return if given email does not match registered email
        if ($email !== $consent->getEmail()) {
            return $this->createErrorResponse('invalidEmail');
        }

        // Early return if consent is already approved
        if ($consent->isApproved()) {
            return $this->createErrorResponse('alreadyApproved');
        }

        // Register consent state
        ConsentManagerRegistry::registerConsent($consent);

        // Approve consent
        $consent->setApproved(true);
        $consent->setApprovalDate(new \DateTime());
        $consent->setValidUntil(null);

        // Dispatch approve event
        $event = new ApproveConsentEvent($consent);
        $this->eventDispatcher->dispatch($event);
        $consent->setOriginalRequestParameters(null);

        // Update approved consent
        $this->consentRepository->update($consent);
        $this->persistenceManager->persistAll();

        return $this->createHtmlResponse($event->getResponse());
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function dismissAction(string $hash, string $email): ResponseInterface
    {
        $consent = $this->consentRepository->findOneByValidationHash($hash);

        // Add template variable
        $this->view->assign('consent', $consent);

        // Early return if consent could not be found
        if ($consent === null) {
            return $this->createErrorResponse('invalidConsent');
        }

        // Early return if given email does not match registered email
        if ($consent->getEmail() !== $email) {
            return $this->createErrorResponse('invalidEmail');
        }

        // Register consent state
        ConsentManagerRegistry::registerConsent($consent);

        // Un-approve consent and obfuscate submitted data
        $consent->setApproved(false);
        $consent->setData(null);
        $consent->setOriginalRequestParameters(null);
        $this->eventDispatcher->dispatch(new DismissConsentEvent($consent));
        $this->consentRepository->update($consent);

        // Remove consent
        $this->consentRepository->remove($consent);
        $this->persistenceManager->persistAll();

        return $this->createResponse();
    }

    protected function createErrorResponse(string $reason): ResponseInterface
    {
        $this->view->assign('error', true);
        $this->view->assign('reason', $reason);

        return $this->createHtmlResponse();
    }

    protected function createHtmlResponse(ResponseInterface $previous = null): ResponseInterface
    {
        if (null === $previous) {
            return $this->createResponse();
        }

        if ($previous->getStatusCode() >= 300) {
            throw new PropagateResponseException($previous, 1645646663);
        }

        $content = (string)$previous->getBody();

        if ('' !== trim($content)) {
            return $this->createResponse($content);
        }

        return $this->createResponse();
    }

    protected function createResponse(string $html = null): ResponseInterface
    {
        // TYPO3 v11+
        if (method_exists($this, 'htmlResponse')) {
            return $this->htmlResponse($html);
        }

        // TYPO3 v10
        return $this->stringableResponseFactory->createResponse()
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody($this->streamFactory->createStream($html ?? $this->view->render()));
    }
}
