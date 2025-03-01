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

use EliasHaeussler\Typo3FormConsent\Domain\Variants\ConsentVariantManager;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Form\Mvc\Persistence\Event\AfterFormDefinitionLoadedEvent;

/**
 * This class takes form finishers from the consent variant and lines them up with the
 * global finishers.
 */

class AfterFormDefinitionLoadedEventListener
{

    public function __construct(
        private readonly ConsentVariantManager $consentVariantManager
    ){}

    #[AsEventListener(
        identifier: 'AfterFormDefinitionLoadedEventListener',
        event: AfterFormDefinitionLoadedEvent::class)
    ]
    public function __invoke(AfterFormDefinitionLoadedEvent $event)
    {
        // Make sure handler does only get called when processing the form definition in backend
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            $event->setFormDefinition(
                $this->consentVariantManager->streamlineFormFinishers(
                    $event->getFormDefinition()
                )
            );
        }
    }
}
