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

namespace EliasHaeussler\Typo3FormConsent\Domain\Factory;

use EliasHaeussler\Typo3FormConsent\Domain\Finishers\FinisherOptions;
use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Event\ModifyConsentEvent;
use EliasHaeussler\Typo3FormConsent\Service\HashService;
use EliasHaeussler\Typo3FormConsent\Type\Transformer\FormRequestTypeTransformer;
use EliasHaeussler\Typo3FormConsent\Type\Transformer\FormValuesTypeTransformer;
use EliasHaeussler\Typo3FormConsent\Type\Transformer\TypeTransformerFactory;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

/**
 * ConsentFactory
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentFactory
{
    private ConfigurationManagerInterface $configurationManager;
    private Context $context;
    private EventDispatcherInterface $eventDispatcher;
    private HashService $hashService;
    private TypeTransformerFactory $typeTransformerFactory;

    public function __construct(
        ConfigurationManagerInterface $configurationManager,
        Context $context,
        EventDispatcherInterface $eventDispatcher,
        HashService $hashService,
        TypeTransformerFactory $typeTransformerFactory
    ) {
        $this->configurationManager = $configurationManager;
        $this->context = $context;
        $this->eventDispatcher = $eventDispatcher;
        $this->hashService = $hashService;
        $this->typeTransformerFactory = $typeTransformerFactory;
    }

    public function createFromForm(FinisherOptions $finisherOptions, FormRuntime $formRuntime): Consent
    {
        $submitDate = $this->getSubmitDate();
        $approvalPeriod = $finisherOptions->getApprovalPeriod();

        $formRequestTransformer = $this->getFormRequestTransformer();
        $formValuesTransformer = $this->getFormValuesTransformer();

        $consent = GeneralUtility::makeInstance(Consent::class)
            ->setEmail($finisherOptions->getRecipientAddress())
            ->setDate($submitDate)
            ->setData($formValuesTransformer->transform($formRuntime))
            ->setFormPersistenceIdentifier($formRuntime->getFormDefinition()->getPersistenceIdentifier())
            ->setOriginalRequestParameters($formRequestTransformer->transform($formRuntime))
            ->setOriginalContentElementUid($this->getCurrentContentElementUid())
            ->setValidUntil($this->calculateExpiryDate($approvalPeriod, $submitDate))
        ;

        if (($storagePid = $finisherOptions->getStoragePid()) > 0) {
            $consent->setPid($storagePid);
        }

        $consent->setValidationHash($this->hashService->generate($consent));

        // Dispatch ModifyConsent event
        $this->eventDispatcher->dispatch(new ModifyConsentEvent($consent));

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

    private function getCurrentContentElementUid(): int
    {
        $contentObjectRenderer = $this->configurationManager->getContentObject();

        if (null !== $contentObjectRenderer) {
            return (int)($contentObjectRenderer->data['uid'] ?? 0);
        }

        return 0;
    }

    private function getFormRequestTransformer(): FormRequestTypeTransformer
    {
        $transformer = $this->typeTransformerFactory->get(FormRequestTypeTransformer::getName());

        \assert($transformer instanceof FormRequestTypeTransformer);

        return $transformer;
    }

    private function getFormValuesTransformer(): FormValuesTypeTransformer
    {
        $transformer = $this->typeTransformerFactory->get(FormValuesTypeTransformer::getName());

        \assert($transformer instanceof FormValuesTypeTransformer);

        return $transformer;
    }
}
