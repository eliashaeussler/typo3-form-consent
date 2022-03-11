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

namespace EliasHaeussler\Typo3FormConsent\Service;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Event\GenerateHashEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HashService
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class HashService
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function generate(Consent $consent): string
    {
        $hashComponents = [
            $consent->getDate()->getTimestamp(),
        ];
        if (null !== $consent->getData()) {
            $hashComponents[] = (string)$consent->getData();
        }
        if (null !== $consent->getValidUntil()) {
            $hashComponents[] = $consent->getValidUntil()->getTimestamp();
        }

        /** @var GenerateHashEvent $event */
        $event = $this->eventDispatcher->dispatch(new GenerateHashEvent($hashComponents, $consent));

        if (null !== ($hash = $event->getHash())) {
            return $hash;
        }

        return GeneralUtility::hmac(implode('_', $event->getComponents()), $consent->getEmail());
    }

    public function isValid(Consent $consent, string $hash = null): bool
    {
        $hash = $hash ?? $consent->getValidationHash();
        $newHash = $this->generate($consent);

        return $hash === $newHash;
    }
}
