<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

use EliasHaeussler\Typo3FormConsent\Form;
use Psr\Http\Message;
use TYPO3\CMS\Core;
use TYPO3\CMS\Form\Mvc;

/**
 * ApplyFinisherVariantsListener
 *
 * Applies consent dependent finishers as form variants during form runtime.
 * The persisted form definition is intentionally left untouched, so the form
 * editor keeps full control over the configured finishers.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class ApplyFinisherVariantsListener
{
    public function __construct(
        private Form\ConsentVariantResolver $consentVariantResolver,
    ) {}

    #[Core\Attribute\AsEventListener('formConsentApplyFinisherVariantsListener')]
    public function __invoke(Mvc\Persistence\Event\AfterFormDefinitionLoadedEvent $event): void
    {
        if (!$this->isFrontendRequest()) {
            return;
        }

        $event->setFormDefinition(
            $this->consentVariantResolver->resolve($event->getFormDefinition()),
        );
    }

    private function isFrontendRequest(): bool
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;

        if (
            !$request instanceof Message\ServerRequestInterface
            || !is_int($request->getAttribute('applicationType'))
        ) {
            return false;
        }

        return Core\Http\ApplicationType::fromRequest($request)->isFrontend();
    }
}
