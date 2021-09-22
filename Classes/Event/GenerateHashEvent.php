<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Event;

/**
 * GenerateHashEvent
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class GenerateHashEvent
{
    /**
     * @var mixed[]
     */
    protected $components = [];

    /**
     * @param mixed[] $components
     */
    public function __construct(array $components)
    {
        $this->components = $components;
    }

    /**
     * @return mixed[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param mixed[]  $components
     * @return self
     */
    public function setComponents(array $components): self
    {
        $this->components = $components;
        return $this;
    }
}
