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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Event\Listener;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * InvokeFinishersListenerTest
 *
 * @author Eric Harrer <info@eric-harrer.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Event\Listener\InvokeFinishersListener::class)]
final class InvokeFinishersListenerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    private const BASE_FRONTEND_URL = 'https://typo3-ext-form-consent.ddev.site/';
    private const FORM_DEFINITION_UID = 1;
    private const FORM_CONTENT_ELEMENT_UID = 1;
    private const CONFIRMATION_PAGE_UID = 2;

    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/form_consent/Build/environment/config/sites' => 'typo3conf/sites',
    ];

    protected array $configurationToUseInTestInstance = [
        'MAIL' => [
            'transport' => 'null',
            'defaultMailFromAddress' => 'no-reply@example.com',
        ],
    ];

    protected function setUp(): void
    {
        // @todo Remove once support for TYPO3 v13 is dropped
        if ((new Core\Information\Typo3Version())->getMajorVersion() < 14) {
            self::markTestSkipped('Database-persisted form definitions are only available in TYPO3 v14 and later.');
        }

        parent::setUp();

        $this->importCSVDataSet(dirname(__DIR__, 2) . '/Fixtures/Database/InvokeFinishersListener/pages.csv');
        $this->importCSVDataSet(dirname(__DIR__, 2) . '/Fixtures/Database/InvokeFinishersListener/tt_content.csv');
        $this->importCSVDataSet(dirname(__DIR__, 2) . '/Fixtures/Database/InvokeFinishersListener/form_definition.csv');
    }

    #[Framework\Attributes\Test]
    public function onConsentApproveInvokesFinishersOfApprovedVariantForDatabasePersistedForm(): void
    {
        $formIdentifier = 'contact-confirmation-approve-variant-' . self::FORM_CONTENT_ELEMENT_UID;

        // Render database-persisted form
        $formResponse = $this->executeFrontendSubRequest(
            new TestingFramework\Core\Functional\Framework\Frontend\InternalRequest(self::BASE_FRONTEND_URL),
        );

        self::assertSame(200, $formResponse->getStatusCode());

        $formHtml = $this->extractFormHtml((string)$formResponse->getBody(), $formIdentifier);

        // Submit form using its generated hidden fields and submit button
        $formValues = $this->extractSubmittedFieldValues($formHtml);
        $formValues['tx_form_formframework'][$formIdentifier]['email-1'] = 'user@example.com';

        $submitRequest = (new TestingFramework\Core\Functional\Framework\Frontend\InternalRequest($this->extractFormAction($formHtml)))
            ->withMethod('POST')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withParsedBody($formValues)
        ;
        $submitRequest->getBody()->write(http_build_query($formValues));
        $submitResponse = $this->executeFrontendSubRequest($submitRequest);

        self::assertSame(200, $submitResponse->getStatusCode());
        self::assertStringContainsString('Please approve your consent.', (string)$submitResponse->getBody());

        // Consent must reference the database-persisted form definition
        $consent = $this->fetchConsent();

        self::assertSame(Src\Enums\ConsentState::New->value, (int)$consent['state']);
        self::assertSame((string)self::FORM_DEFINITION_UID, $consent['form_persistence_identifier']);
        self::assertSame(self::FORM_CONTENT_ELEMENT_UID, (int)$consent['original_content_element_uid']);
        self::assertNotEmpty($consent['original_request_parameters']);

        // Approve consent
        $approveUri = $this->get(Core\Site\SiteFinder::class)
            ->getSiteByPageId(self::CONFIRMATION_PAGE_UID)
            ->getRouter()
            ->generateUri(self::CONFIRMATION_PAGE_UID, [
                'tx_formconsent_consent' => [
                    'action' => 'approve',
                    'controller' => 'Consent',
                    'hash' => $consent['validation_hash'],
                    'email' => $consent['email'],
                ],
            ])
        ;

        // Approval is a plain frontend request without any backend user session
        self::assertNull($GLOBALS['BE_USER'] ?? null);

        $approveResponse = $this->executeFrontendSubRequest(
            new TestingFramework\Core\Functional\Framework\Frontend\InternalRequest((string)$approveUri),
            new TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext(),
        );

        self::assertSame(200, $approveResponse->getStatusCode());

        $approvedConsent = $this->fetchConsent();

        self::assertSame(Src\Enums\ConsentState::Approved->value, (int)$approvedConsent['state']);
        self::assertNull($approvedConsent['valid_until']);

        // Finisher of "isConsentApproved()" variant must be invoked
        self::assertStringContainsString('Thanks for your consent.', (string)$approveResponse->getBody());
    }

    private function extractFormHtml(string $html, string $formIdentifier): string
    {
        $pattern = sprintf('#<form\b[^>]*\bid="%s"[^>]*>.*?</form>#s', preg_quote($formIdentifier, '#'));

        if (preg_match($pattern, $html, $matches) !== 1) {
            self::fail(sprintf('Form "%s" was not rendered.', $formIdentifier));
        }

        return $matches[0];
    }

    private function extractFormAction(string $formHtml): string
    {
        if (preg_match('#^<form\b[^>]*\baction="([^"]+)"#', $formHtml, $matches) !== 1) {
            self::fail('Form action could not be determined.');
        }

        $action = htmlspecialchars_decode($matches[1]);

        if (str_starts_with($action, '/')) {
            return rtrim(self::BASE_FRONTEND_URL, '/') . $action;
        }

        return $action;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function extractSubmittedFieldValues(string $formHtml): array
    {
        // Hidden fields (form state, session, trusted properties) and the submit button,
        // which carries the "__currentPage" value, are sent the same way a browser would
        preg_match_all('#<input\b[^>]*\btype="hidden"[^>]*>|<button\b[^>]*\btype="submit"[^>]*>#', $formHtml, $matches);

        self::assertMatchesRegularExpression('#<button\b[^>]*\btype="submit"[^>]*\bname="[^"]*\[__currentPage\]"#', $formHtml);

        $queryParts = [];

        foreach ($matches[0] as $input) {
            if (preg_match('#\bname="([^"]*)"#', $input, $name) !== 1) {
                continue;
            }

            preg_match('#\bvalue="([^"]*)"#', $input, $value);

            $queryParts[] = rawurlencode(htmlspecialchars_decode($name[1]))
                . '=' . rawurlencode(htmlspecialchars_decode($value[1] ?? ''));
        }

        parse_str(implode('&', $queryParts), $values);

        self::assertNotEmpty($values['tx_form_formframework'] ?? null);

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchConsent(): array
    {
        $rows = $this->getConnectionPool()
            ->getConnectionForTable(Src\Domain\Model\Consent::TABLE_NAME)
            ->select(['*'], Src\Domain\Model\Consent::TABLE_NAME)
            ->fetchAllAssociative()
        ;

        self::assertCount(1, $rows);

        return $rows[0];
    }
}
