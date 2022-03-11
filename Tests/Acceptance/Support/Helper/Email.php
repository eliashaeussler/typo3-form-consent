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

/**
 * Email
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class Email extends Module
{
    public function grabUrlFromEmailBody(int $index = 0): string
    {
        $urls = $this->extractUrlsFromEmailBody();

        if (!isset($urls[$index])) {
            $this->fail(
                sprintf('Only %d urls were extracted from email body, index %d requested.', \count($urls), $index)
            );
        }

        return $urls[$index];
    }

    /**
     * @return list<string>
     */
    public function grabAllUrlsFromEmailBody(): array
    {
        return $this->extractUrlsFromEmailBody();
    }

    public function seeUrlsInEmailBody(int $count = null): void
    {
        if (!$this->hasModule('Asserts')) {
            $this->fail('Asserts module is not enabled.');
        }

        /** @var Module\Asserts $I */
        $I = $this->getModule('Asserts');

        $urls = $this->extractUrlsFromEmailBody();

        if (null !== $count) {
            $I->assertCount($count, $urls);
        } else {
            $I->assertNotEmpty($urls);
        }
    }

    /**
     * @return list<string>
     */
    private function extractUrlsFromEmailBody(): array
    {
        if (!$this->hasModule('MailHog')) {
            $this->fail('MailHog module is not enabled.');
        }

        /** @var Module\MailHog $I */
        $I = $this->getModule('MailHog');

        $body = quoted_printable_decode($I->grabBodyFromEmail('text/plain'));
        $urlPattern = sprintf('~%s(?P<url>\S+)~', preg_quote($this->getCurrentBaseUrl(), '~'));

        if (!preg_match_all($urlPattern, $body, $matches)) {
            $this->fail('No urls found in email.');
        }

        return array_map(fn (string $url): string => '/' . $url, array_values(array_filter($matches['url'])));
    }

    private function getCurrentBaseUrl(): string
    {
        if (!$this->hasModule('WebDriver')) {
            $this->fail('WebDriver module is not enabled.');
        }

        /** @var Module\WebDriver $webDriver */
        $webDriver = $this->getModule('WebDriver');

        return $webDriver->_getUrl();
    }
}
