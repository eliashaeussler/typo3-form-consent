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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Helper;

use Codeception\Module;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;

/**
 * Url
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Url extends Module
{
    /**
     * @return array<string, mixed>
     */
    public function extractQueryParametersFromUrl(string $url): array
    {
        $queryParams = [];
        $parsedUrl = parse_url($url);

        \assert(\is_array($parsedUrl));

        parse_str($parsedUrl['query'] ?? '', $queryParams);

        return $queryParams;
    }

    public function assertUrlPathEquals(string $expected, string $url): void
    {
        if (!$this->hasModule('Asserts')) {
            $this->fail('Asserts module is not enabled.');
        }

        /** @var Module\Asserts $I */
        $I = $this->getModule('Asserts');

        $actual = parse_url($url, PHP_URL_PATH);

        $I->assertEquals($expected, $actual);
    }

    public function assertQueryParameterEquals(string $expected, string $url, string $path): void
    {
        if (!$this->hasModule('Asserts')) {
            $this->fail('Asserts module is not enabled.');
        }

        /** @var Module\Asserts $I */
        $I = $this->getModule('Asserts');

        try {
            $queryParams = $this->extractQueryParametersFromUrl($url);
            $actual = ArrayUtility::getValueByPath($queryParams, $path);

            $I->assertEquals($expected, $actual);
        } catch (MissingArrayPathException $exception) {
            $this->fail(
                sprintf('Query parameter "%s" was not found in URL: %s', $path, $exception->getMessage())
            );
        }
    }
}
