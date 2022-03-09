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

namespace EliasHaeussler\Typo3FormConsent\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use EliasHaeussler\Typo3FormConsent\Domain\Factory\ConsentFactory;
use EliasHaeussler\Typo3FormConsent\Event\ModifyConsentMailEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
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
final class ConsentFinisher extends AbstractFinisher implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ConsentFactory $consentFactory;
    private EventDispatcherInterface $eventDispatcher;
    private Mailer $mailer;
    private PersistenceManagerInterface $persistenceManager;

    // @todo Move to constructor once v10 support is dropped
    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    // @todo Move to constructor once v10 support is dropped
    public function injectMailer(Mailer $mailer): void
    {
        $this->mailer = $mailer;
    }

    // @todo Move to constructor once v10 support is dropped
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    public function __construct(string $finisherIdentifier = '')
    {
        /* @phpstan-ignore-next-line */
        parent::__construct($finisherIdentifier);

        // @todo Use dependency injection once v10 support is dropped
        $this->consentFactory = GeneralUtility::makeInstance(ConsentFactory::class);
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
        $this->eventDispatcher->dispatch(new ModifyConsentMailEvent($mail));

        // Send mail
        try {
            $this->mailer->send($mail);
        } catch (TransportExceptionInterface $e) {
            throw new FinisherException(
                Localization::forKey('consentMail.error', null, true),
                1577109483
            );
        }
    }

    private function initializeMail(FinisherOptions $finisherOptions): FluidEmail
    {
        // Initialize mail
        $mail = GeneralUtility::makeInstance(FluidEmail::class, $finisherOptions->getTemplatePaths())
            ->to(new Address($finisherOptions->getRecipientAddress(), $finisherOptions->getRecipientName()))
            ->subject($finisherOptions->getSubject())
            ->setTemplate('ConsentMail')
            ->assign('formRuntime', $this->finisherContext->getFormRuntime())
            ->assign('showDismissLink', $finisherOptions->getShowDismissLink())
            ->assign('confirmationPid', $finisherOptions->getConfirmationPid());

        // Set the PSR-7 request object if available
        $serverRequest = $this->getServerRequest();
        if (null !== $serverRequest) {
            $mail->setRequest($serverRequest);
        }

        return $mail;
    }

    private function addFlashMessage(\Exception $exception, bool $cancel = true): void
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();
        $flashMessageFinisher = $formDefinition->createFinisher('FlashMessage', [
            'messageBody' => $exception->getMessage(),
            'messageCode' => $exception->getCode(),
            'severity' => AbstractMessage::ERROR,
        ]);
        $flashMessageFinisher->execute($this->finisherContext);

        // Cancel execution
        if ($cancel) {
            $this->finisherContext->cancel();
        }
    }

    private function getServerRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
