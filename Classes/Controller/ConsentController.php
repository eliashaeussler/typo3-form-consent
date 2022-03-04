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
use Psr\Http\Message\ResponseInterface;
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

    public function __construct(ConsentRepository $consentRepository, PersistenceManagerInterface $persistenceManager)
    {
        $this->consentRepository = $consentRepository;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function approveAction(string $hash, string $email): ?ResponseInterface
    {
        $consent = $this->consentRepository->findOneByValidationHash($hash);

        // Add template variable
        $this->view->assign('consent', $consent);

        // Early return if consent could not be found
        if (null === $consent) {
            return $this->renderError('invalidConsent');
        }

        // Early return if given email does not match registered email
        if ($email !== $consent->getEmail()) {
            return $this->renderError('invalidEmail');
        }

        // Early return if consent is already approved
        if ($consent->isApproved()) {
            return $this->renderError('alreadyApproved');
        }

        // Approve consent
        $consent->setApproved(true);
        $consent->setApprovalDate(new \DateTime());
        $consent->setValidUntil(null);
        $this->eventDispatcher->dispatch(new ApproveConsentEvent($consent));
        $this->consentRepository->update($consent);

        return $this->renderViewAsResponse();
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function dismissAction(string $hash, string $email): ?ResponseInterface
    {
        $consent = $this->consentRepository->findOneByValidationHash($hash);

        // Add template variable
        $this->view->assign('consent', $consent);

        // Early return if consent could not be found
        if ($consent === null) {
            return $this->renderError('invalidConsent');
        }

        // Early return if given email does not match registered email
        if ($consent->getEmail() !== $email) {
            return $this->renderError('invalidEmail');
        }

        // Un-approve consent and obfuscate submitted data
        $consent->setApproved(false);
        $consent->setData([]);
        $this->eventDispatcher->dispatch(new DismissConsentEvent($consent));
        $this->consentRepository->update($consent);

        // Remove consent
        $this->consentRepository->remove($consent);
        $this->persistenceManager->persistAll();

        return $this->renderViewAsResponse();
    }

    protected function renderError(string $reason): ?ResponseInterface
    {
        $this->view->assign('error', true);
        $this->view->assign('reason', $reason);

        return $this->renderViewAsResponse();
    }

    protected function renderViewAsResponse(): ?ResponseInterface
    {
        if (method_exists($this, 'htmlResponse')) {
            return $this->htmlResponse();
        }

        // For TYPO3 v10 compatibility, we return NULL in order to render
        // the view from ActionController::callActionMethod().
        return null;
    }
}
