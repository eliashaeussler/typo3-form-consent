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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Extension;

use Codeception\Events;
use Codeception\Extension;

/**
 * ApplicationEntrypointModifier
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
abstract class ApplicationEntrypointModifier extends Extension
{
    /**
     * @var array<string, string>
     */
    protected static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
    ];

    protected string $targetDirectory;
    protected string $buildDirectory;
    protected string $mainEntrypoint;
    protected string $appEntrypoint;
    protected string $testEntrypoint;

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $options
     */
    public function __construct($config, $options, string $targetDirectory, string $entrypointFile)
    {
        parent::__construct($config, $options);

        $this->targetDirectory = $targetDirectory;
        $this->buildDirectory = \dirname(__DIR__, 3) . '/Build';
        $this->mainEntrypoint = $this->targetDirectory . '/index.php';
        $this->appEntrypoint = $this->targetDirectory . '/app.php';
        $this->testEntrypoint = $this->buildDirectory . '/' . ltrim($entrypointFile, '/');
    }

    public function beforeSuite(): void
    {
        $this->assertFilesExist();

        if ($this->entrypointNeedsUpdate()) {
            $this->moveEntrypoint();
        }
    }

    protected function assertFilesExist(): void
    {
        \assert(is_dir($this->targetDirectory));
        \assert(file_exists($this->mainEntrypoint));
        \assert(file_exists($this->testEntrypoint));
    }

    protected function entrypointNeedsUpdate(): bool
    {
        if (!file_exists($this->appEntrypoint)) {
            return true;
        }

        return sha1_file($this->mainEntrypoint) !== sha1_file($this->testEntrypoint);
    }

    protected function moveEntrypoint(): void
    {
        if (!file_exists($this->appEntrypoint)) {
            rename($this->mainEntrypoint, $this->appEntrypoint);
        }

        copy($this->testEntrypoint, $this->mainEntrypoint);
    }
}
