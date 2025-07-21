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

namespace EliasHaeussler\Typo3FormConsent\Controller;

use EliasHaeussler\Typo3FormConsent\Domain;
use EliasHaeussler\Typo3FormConsent\Event;
use EliasHaeussler\Typo3FormConsent\Registry;
use Psr\Http\Message;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

/**
 * ConsentController
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentController extends Extbase\Mvc\Controller\ActionController
{
    public function __construct(
        private readonly Domain\Repository\ConsentRepository $consentRepository,
        private readonly Extbase\Persistence\PersistenceManagerInterface $persistenceManager,
    ) {}

    public function initializeAction(): void
    {
        if ($this->isPreviewRequested()) {
            $this->actionMethodName = 'previewAction';
            $this->request = $this->request->withControllerActionName('preview');
            $this->arguments->removeAll();
        }
    }

    /**
     * @throws Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws Core\Http\PropagateResponseException
     * @throws Extbase\Persistence\Exception\UnknownObjectException
     */
    public function approveAction(string $hash, string $email, bool $verify = false): Message\ResponseInterface
    {
        $consent = $this->consentRepository->findOneByValidationHash($hash);

        // Add template variable
        $this->view->assign('consent', $consent);

        // Early return if consent could not be found
        if ($consent === null) {
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

        // Render required user verification button
        if ($verify) {
            $this->view->assign('verificationNeeded', true);

            return $this->createHtmlResponse();
        }

        // Register consent state
        Registry\ConsentManagerRegistry::registerConsent($consent);

        // Approve consent
        $consent->setApproved();
        $consent->setValidUntil(null);

        // Dispatch approve event
        try {
            $event = new Event\ApproveConsentEvent($consent);
            $this->eventDispatcher->dispatch($event);
        } catch (\Exception $exception) {
            return $this->createErrorResponse('unexpectedError', $exception);
        }

        // Update approved consent
        $this->consentRepository->update($consent);
        $this->persistenceManager->persistAll();

        return $this->createHtmlResponse($event->getResponse());
    }

    /**
     * @throws Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws Core\Http\PropagateResponseException
     * @throws Extbase\Persistence\Exception\UnknownObjectException
     */
    public function dismissAction(string $hash, string $email, bool $verify = false): Message\ResponseInterface
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

        // Render required user verification button
        if ($verify) {
            $this->view->assign('verificationNeeded', true);

            return $this->createHtmlResponse();
        }

        // Register consent state
        Registry\ConsentManagerRegistry::registerConsent($consent);

        // Un-approve consent
        $consent->setDismissed();
        $consent->setValidUntil(null);

        // Dispatch dismiss event
        try {
            $event = new Event\DismissConsentEvent($consent);
            $this->eventDispatcher->dispatch($event);
        } catch (\Exception $exception) {
            return $this->createErrorResponse('unexpectedError', $exception);
        }

        // Obfuscate submitted data
        $consent->setData(null);
        $consent->setOriginalRequestParameters(null);

        // Remove dismissed consent
        $this->consentRepository->update($consent);
        $this->consentRepository->remove($consent);
        $this->persistenceManager->persistAll();

        return $this->createHtmlResponse($event->getResponse());
    }

    /**
     * Dummy preview action for use in backend context.
     *
     * This action is not part of any frontend plugin. It is used as dummy action for
     * preview requests during an active backend session.
     *
     * NOTE: Method must not be private, otherwise action is not callable by ActionController.
     *
     * @see ConsentController::initializeAction()
     * @see ConsentController::isPreviewRequested()
     */
    protected function previewAction(): Message\ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * @throws Core\Http\PropagateResponseException
     */
    private function createErrorResponse(string $reason, \Throwable $exception = null): Message\ResponseInterface
    {
        $this->view->assign('error', true);
        $this->view->assign('reason', $reason);
        $this->view->assign('exception', $exception);

        return $this->createHtmlResponse();
    }

    /**
     * @throws Core\Http\PropagateResponseException
     */
    private function createHtmlResponse(Message\ResponseInterface $previous = null): Message\ResponseInterface
    {
        if ($previous === null) {
            return $this->htmlResponse();
        }

        if ($previous->getStatusCode() >= 300) {
            throw new Core\Http\PropagateResponseException($previous, 1645646663);
        }

        $content = (string)$previous->getBody();

        if (trim($content) !== '') {
            return $this->htmlResponse($content);
        }

        return $this->htmlResponse();
    }

    private function isPreviewRequested(): bool
    {
        // Early return if no backend session is active
        if ($this->getBackendUser() === null) {
            return false;
        }

        // Early return if at least one argument is given
        if ($this->request->getArguments() !== []) {
            return false;
        }

        return true;
    }

    private function getBackendUser(): ?Core\Authentication\BackendUserAuthentication
    {
        $backendUser = $GLOBALS['BE_USER'] ?? null;

        if ($backendUser instanceof Core\Authentication\BackendUserAuthentication) {
            return $backendUser;
        }

        return null;
    }
}
