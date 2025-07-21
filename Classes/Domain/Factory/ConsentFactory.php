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

namespace EliasHaeussler\Typo3FormConsent\Domain\Factory;

use EliasHaeussler\Typo3FormConsent\Domain;
use EliasHaeussler\Typo3FormConsent\Event;
use EliasHaeussler\Typo3FormConsent\Service;
use EliasHaeussler\Typo3FormConsent\Type;
use Psr\EventDispatcher;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;

/**
 * ConsentFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentFactory
{
    public function __construct(
        private readonly Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
        private readonly Core\Context\Context $context,
        private readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
        private readonly Type\Transformer\FormRequestTypeTransformer $formRequestTypeTransformer,
        private readonly Type\Transformer\FormValuesTypeTransformer $formValuesTypeTransformer,
        private readonly Service\HashService $hashService,
    ) {}

    public function createFromForm(
        Domain\Finishers\FinisherOptions $finisherOptions,
        Form\Domain\Finishers\FinisherContext $finisherContext,
    ): Domain\Model\Consent {
        $formRuntime = $finisherContext->getFormRuntime();
        $submitDate = $this->getSubmitDate();
        $approvalPeriod = $finisherOptions->getApprovalPeriod();

        $consent = Core\Utility\GeneralUtility::makeInstance(Domain\Model\Consent::class)
            ->setEmail($finisherOptions->getRecipientAddress())
            ->setDate($submitDate)
            ->setData($this->formValuesTypeTransformer->transform($formRuntime))
            ->setFormPersistenceIdentifier($formRuntime->getFormDefinition()->getPersistenceIdentifier())
            ->setOriginalRequestParameters($this->formRequestTypeTransformer->transform($formRuntime))
            ->setOriginalContentElementUid($this->getCurrentContentElementUid($formRuntime->getRequest()))
            ->setState(Type\ConsentStateType::createNew())
            ->setValidUntil($this->calculateExpiryDate($approvalPeriod, $submitDate))
        ;

        if (($storagePid = $finisherOptions->getStoragePid()) > 0) {
            $consent->setPid($storagePid);
        }

        $consent->setValidationHash($this->hashService->generate($consent));

        // Dispatch ModifyConsent event
        $this->eventDispatcher->dispatch(new Event\ModifyConsentEvent($consent, $finisherContext));

        // Re-generate validation hash if consent has changed in the meantime
        if (!$this->hashService->isValid($consent)) {
            $consent->setValidationHash($this->hashService->generate($consent));
        }

        return $consent;
    }

    private function getSubmitDate(): \DateTime
    {
        return new \DateTime('@' . $this->context->getPropertyFromAspect('date', 'timestamp', time()));
    }

    private function calculateExpiryDate(int $approvalPeriod, \DateTime $submitDate): ?\DateTime
    {
        // Early return if invalid approval period is given
        if ($approvalPeriod <= 0) {
            return null;
        }

        $target = $submitDate->getTimestamp() + $approvalPeriod;

        return new \DateTime('@' . $target);
    }

    private function getCurrentContentElementUid(Extbase\Mvc\RequestInterface $request): int
    {
        $contentObjectRenderer = $request->getAttribute('currentContentObject');

        // @todo Remove if support for TYPO3 v11 is dropped
        if ($contentObjectRenderer === null) {
            $contentObjectRenderer = $this->configurationManager->getContentObject();
        }

        if ($contentObjectRenderer !== null) {
            return (int)($contentObjectRenderer->data['uid'] ?? 0);
        }

        return 0;
    }
}
