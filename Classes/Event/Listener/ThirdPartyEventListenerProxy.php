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

namespace EliasHaeussler\Typo3FormConsent\Event\Listener;

use Derhansen\FormCrshield;
use EliasHaeussler\Typo3FormConsent\Registry;
use TYPO3\CMS\Core;
use TYPO3\CMS\Form;

/**
 * ThirdPartyEventListenerProxy
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class ThirdPartyEventListenerProxy
{
    public function __construct(
        private FormCrshield\EventListener\FormCrShield $formCrShield,
    ) {}

    #[Core\Attribute\AsEventListener('formConsentThirdPartyEventListenerProxyAfterCurrentPageIsResolved')]
    public function afterCurrentPageIsResolved(Form\Event\AfterCurrentPageIsResolvedEvent $event): void
    {
        if ($this->shouldExecuteThirdPartyListeners($event->formRuntime)) {
            $this->formCrShield->afterInitializeCurrentPage($event);
        }
    }

    #[Core\Attribute\AsEventListener('formConsentThirdPartyEventListenerProxyBeforeRenderableIsValidated')]
    public function beforeRenderableIsValidated(Form\Event\BeforeRenderableIsValidatedEvent $event): void
    {
        if ($this->shouldExecuteThirdPartyListeners($event->formRuntime)) {
            $this->formCrShield->afterSubmit($event);
        }
    }

    private function shouldExecuteThirdPartyListeners(Form\Domain\Runtime\FormRuntime $formRuntime): bool
    {
        $persistenceIdentifier = $formRuntime->getFormDefinition()->getPersistenceIdentifier();

        return !Registry\ConsentManagerRegistry::isConsentApproved($persistenceIdentifier) &&
            !Registry\ConsentManagerRegistry::isConsentDismissed($persistenceIdentifier)
        ;
    }
}
