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

namespace EliasHaeussler\Typo3FormConsent\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use EliasHaeussler\Typo3FormConsent\Domain\Factory\ConsentFactory;
use EliasHaeussler\Typo3FormConsent\Event\ModifyConsentMailEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\Exception\FinisherException;
use TYPO3\CMS\Form\ViewHelpers\RenderRenderableViewHelper;

/**
 * ConsentFinisher
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentFinisher extends AbstractFinisher
{
    public function __construct(
        private readonly ConsentFactory $consentFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Mailer $mailer,
        private readonly PersistenceManagerInterface $persistenceManager,
    ) {
    }

    /**
     * @throws IllegalObjectTypeException
     */
    protected function executeInternal(): ?string
    {
        try {
            $this->executeConsent();
        } catch (FinisherException $exception) {
            $this->addFlashMessage($exception);
        }

        return null;
    }

    /**
     * @throws FinisherException
     * @throws \Exception
     */
    private function executeConsent(): void
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $finisherOptions = new FinisherOptions(fn (string $optionName) => $this->parseOption($optionName));

        // Create consent
        $consent = $this->consentFactory->createFromForm($finisherOptions, $formRuntime);

        // Persist consent
        $this->persistenceManager->add($consent);
        $this->persistenceManager->persistAll();

        // Add consent to finisher context
        $this->finisherContext->getFinisherVariableProvider()->add(
            $this->shortFinisherIdentifier,
            'lastInsertedConsent',
            $consent
        );

        // Build mail
        $mail = $this->initializeMail($finisherOptions);
        $mail->assign('consent', $consent);

        if ('' !== ($senderAddress = $finisherOptions->getSenderAddress())) {
            $mail->from(new Address($senderAddress, $finisherOptions->getSenderName()));
        }

        // Provide form runtime as view helper variable to allow usage of
        // various form view helpers. This for example allows to list all
        // submitted form values using the <formvh:renderAllFormValues>
        // view helper.
        $mail->getViewHelperVariableContainer()
            ->add(RenderRenderableViewHelper::class, 'formRuntime', $formRuntime);

        // Dispatch ModifyConsentMail event
        $this->eventDispatcher->dispatch(new ModifyConsentMailEvent($mail, $formRuntime));

        // Send mail
        try {
            $this->mailer->send($mail);
        } catch (TransportExceptionInterface) {
            throw new FinisherException(
                Localization::forKey('consentMail.error', null, true),
                1577109483
            );
        }
    }

    private function initializeMail(FinisherOptions $finisherOptions): FluidEmail
    {
        return GeneralUtility::makeInstance(FluidEmail::class, $finisherOptions->getTemplatePaths())
            ->to(new Address($finisherOptions->getRecipientAddress(), $finisherOptions->getRecipientName()))
            ->subject($finisherOptions->getSubject())
            ->setTemplate('ConsentMail')
            ->assign('formRuntime', $this->finisherContext->getFormRuntime())
            ->assign('showDismissLink', $finisherOptions->getShowDismissLink())
            ->assign('confirmationPid', $finisherOptions->getConfirmationPid())
            ->setRequest($this->finisherContext->getRequest())
        ;
    }

    private function addFlashMessage(\Exception $exception): void
    {
        if (class_exists(ContextualFeedbackSeverity::class)) {
            $severity = ContextualFeedbackSeverity::ERROR;
        } else {
            // @todo Remove once support for TYPO3 v11 is dropped
            $severity = AbstractMessage::ERROR;
        }

        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();
        $flashMessageFinisher = $formDefinition->createFinisher('FlashMessage', [
            'messageBody' => $exception->getMessage(),
            'messageCode' => $exception->getCode(),
            'severity' => $severity,
        ]);
        $flashMessageFinisher->execute($this->finisherContext);

        // Cancel execution
        $this->finisherContext->cancel();
    }
}
