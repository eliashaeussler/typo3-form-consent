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

namespace EliasHaeussler\Typo3FormConsent\DependencyInjection;

use Derhansen\FormCrshield;
use EliasHaeussler\Typo3FormConsent\Event;
use Symfony\Component\DependencyInjection;

/**
 * ThirdPartyServicePass
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 * @codeCoverageIgnore
 *
 * @todo Remove once support for EXT:form_crshield v3 / TYPO3 v13 is dropped
 */
final readonly class ThirdPartyServicePass implements DependencyInjection\Compiler\CompilerPassInterface
{
    public function process(DependencyInjection\ContainerBuilder $container): void
    {
        if ($container->hasDefinition(FormCrshield\EventListener\FormCrShield::class)) {
            $definition = new DependencyInjection\Definition(Event\Listener\ThirdPartyEventListenerProxy::class);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);

            $container->getDefinition(FormCrshield\EventListener\FormCrShield::class)->setAutoconfigured(false);
            $container->setDefinition(Event\Listener\ThirdPartyEventListenerProxy::class, $definition);
        }
    }
}
