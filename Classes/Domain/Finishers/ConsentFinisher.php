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

namespace EliasHaeussler\Typo3FormConsent\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent\Domain;
use EliasHaeussler\Typo3FormConsent\Event;
use Psr\EventDispatcher;
use Symfony\Component\Mailer;
use Symfony\Component\Mime;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;

/**
 * ConsentFinisher
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentFinisher extends Form\Domain\Finishers\AbstractFinisher
{
    private ?Core\Localization\LanguageService $languageService = null;

    public function __construct(
        private readonly Domain\Factory\ConsentFactory $consentFactory,
        private readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
        private readonly Core\Mail\Mailer $mailer,
        private readonly Extbase\Persistence\PersistenceManagerInterface $persistenceManager,
        private readonly Core\Localization\LanguageServiceFactory $languageServiceFactory,
    ) {}

    /**
     * @throws Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function executeInternal(): ?string
    {
        try {
            $this->executeConsent();
        } catch (Form\Domain\Finishers\Exception\FinisherException $exception) {
            $this->addFlashMessage($exception);
        }

        return null;
    }

    /**
     * @throws Form\Domain\Finishers\Exception\FinisherException
     * @throws \Exception
     */
    private function executeConsent(): void
    {
        $formRuntime = $this->finisherContext->getFormRuntime();
        $finisherOptions = new FinisherOptions(
            fn(string $optionName) => $this->parseOption($optionName),
            $formRuntime->getRequest(),
        );

        // Create consent
        $consent = $this->consentFactory->createFromForm($finisherOptions, $this->finisherContext);

        // Exit if finisher execution is cancelled
        if ($this->finisherContext->isCancelled()) {
            return;
        }

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
            $mail->from(new Mime\Address($senderAddress, $finisherOptions->getSenderName()));
        }

        if ('' !== ($replyToAddress = $finisherOptions->getReplyToAddress())) {
            $mail->replyTo(new Mime\Address($replyToAddress, $finisherOptions->getReplyToName()));
        }

        // Provide form runtime as view helper variable to allow usage of
        // various form view helpers. This for example allows to list all
        // submitted form values using the <formvh:renderAllFormValues>
        // view helper.
        $mail->getViewHelperVariableContainer()
            ->add(Form\ViewHelpers\RenderRenderableViewHelper::class, 'formRuntime', $formRuntime);

        // Dispatch ModifyConsentMail event
        $this->eventDispatcher->dispatch(new Event\ModifyConsentMailEvent($mail, $formRuntime));

        // Send mail
        try {
            $this->mailer->send($mail);
        } catch (Mailer\Exception\TransportExceptionInterface) {
            throw new Form\Domain\Finishers\Exception\FinisherException(
                $this->translate('LLL:EXT:form_consent/Resources/Private/Language/locallang.xlf:consentMail.error'),
                1577109483,
            );
        }
    }

    private function initializeMail(FinisherOptions $finisherOptions): Core\Mail\FluidEmail
    {
        return Core\Utility\GeneralUtility::makeInstance(Core\Mail\FluidEmail::class, $finisherOptions->getTemplatePaths())
            ->to(new Mime\Address($finisherOptions->getRecipientAddress(), $finisherOptions->getRecipientName()))
            ->subject($finisherOptions->getSubject())
            ->setTemplate('ConsentMail')
            ->assign('formRuntime', $this->finisherContext->getFormRuntime())
            ->assign('showDismissLink', $finisherOptions->getShowDismissLink())
            ->assign('confirmationPid', $finisherOptions->getConfirmationPid())
            ->assign('requireApproveVerification', $finisherOptions->requiresVerificationForApproval())
            ->assign('requireDismissVerification', $finisherOptions->requiresVerificationForDismissal())
            ->setRequest($this->finisherContext->getRequest())
        ;
    }

    private function addFlashMessage(\Exception $exception): void
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();
        $flashMessageFinisher = $formDefinition->createFinisher('FlashMessage', [
            'messageBody' => $exception->getMessage(),
            'messageCode' => $exception->getCode(),
            'severity' => Core\Type\ContextualFeedbackSeverity::ERROR,
        ]);
        $flashMessageFinisher->execute($this->finisherContext);

        // Cancel execution
        $this->finisherContext->cancel();
    }

    private function translate(string $key): string
    {
        $this->languageService ??= $this->languageServiceFactory->createFromUserPreferences($this->getBackendUser());

        return $this->languageService->sL($key);
    }

    private function getBackendUser(): Core\Authentication\BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
