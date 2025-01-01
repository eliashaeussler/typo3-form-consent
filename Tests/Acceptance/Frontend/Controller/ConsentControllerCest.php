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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Frontend\Controller;

use EliasHaeussler\Typo3FormConsent as Src;
use EliasHaeussler\Typo3FormConsent\Tests;
use TYPO3\CMS\Core;

/**
 * ConsentControllerCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentControllerCest
{
    private string $approveUrl;
    private string $dismissUrl;

    public function canApproveConsentViaMail(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I);

        $queryParams = $I->extractQueryParametersFromUrl($this->approveUrl);
        $hash = $queryParams['tx_formconsent_consent']['hash'] ?? null;

        // @todo Remove once support for TYPO3 v12 is dropped
        $typo3Version = new Core\Information\Typo3Version();
        $expectedValidUntil = $typo3Version->getMajorVersion() >= 13 ? 0 : null;

        $I->amOnPage($this->approveUrl);

        $I->see('Consent successful');

        $I->seeInDatabase(
            Src\Domain\Model\Consent::TABLE_NAME,
            [
                'data' => '{"email-1":"user@example.com","fileupload-1":null}',
                'email' => 'user@example.com',
                'state' => Src\Enums\ConsentState::Approved->value,
                'form_persistence_identifier' => '1:/form_definitions/contact.form.yaml',
                'original_content_element_uid' => 1,
                'original_request_parameters !=' => null,
                'validation_hash' => $hash,
                'valid_until' => $expectedValidUntil,
            ]
        );
    }

    public function canDismissConsentViaMail(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I);

        $queryParams = $I->extractQueryParametersFromUrl($this->dismissUrl);
        $hash = $queryParams['tx_formconsent_consent']['hash'] ?? null;

        $I->amOnPage($this->dismissUrl);

        $I->see('Consent successfully revoked');

        $I->seeInDatabase(
            Src\Domain\Model\Consent::TABLE_NAME,
            [
                'deleted' => '1',
                'data' => null,
                'email' => 'user@example.com',
                'state' => Src\Enums\ConsentState::Dismissed->value,
                'form_persistence_identifier' => '1:/form_definitions/contact.form.yaml',
                'original_content_element_uid' => 1,
                'original_request_parameters' => null,
                'validation_hash' => $hash,
            ]
        );
    }

    public function canApproveConsentAndInvokeConfirmationFinisher(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::CONFIRMATION_AFTER_APPROVE);

        $I->amOnPage($this->approveUrl);

        $I->see('Thanks for your consent.');
    }

    public function canApproveConsentAndInvokeEmailFinisher(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::EMAIL_AFTER_APPROVE);

        $I->amOnPage($this->approveUrl);

        $I->fetchEmails();
        $I->accessInboxFor('admin@example.com');

        $I->haveNumberOfUnreadEmails(1);
        $I->openNextUnreadEmail();
        $I->seeInOpenedEmailSubject('Consent approved');
    }

    public function canApproveConsentAndInvokeRedirectFinisher(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::REDIRECT_AFTER_APPROVE);

        $I->amOnPage($this->approveUrl);

        $I->seeCurrentUrlEquals('/');
    }

    public function canApproveConsentAndMigrateHmacHashesBeforeInvokingFinishers(
        Tests\Acceptance\Support\AcceptanceTester $I,
    ): void {
        // @todo Remove once support for TYPO3 v12 is dropped
        if ((new Core\Information\Typo3Version())->getMajorVersion() < 13) {
            $I->markTestSkipped('Test can be executed on TYPO3 >= 13 only.');
        }

        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::CONFIRMATION_AFTER_APPROVE, true);

        $queryParams = $I->extractQueryParametersFromUrl($this->approveUrl);
        $hash = $queryParams['tx_formconsent_consent']['hash'] ?? null;

        $I->assertIsString($hash);

        $originalRequestParameters = new Src\Type\JsonType(
            $I->grabFromDatabase(
                Src\Domain\Model\Consent::TABLE_NAME,
                'original_request_parameters',
                ['validation_hash' => $hash],
            ),
        );
        $parameters = $originalRequestParameters->toArray();
        $parametersToMigrate = [
            '__state',
            '__trustedProperties',
            'resourcePointer',
        ];

        // Set encryption key (as defined in Tests/Build/Configuration/system/additional.php)
        // This is needed to properly run HMAC generation since it uses encryption key as additional secret
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = '22be11b3acb2d0a7427e9f23c6c1d8d2c19b05312d4961c025b9a8b74bd7f4087ad38eca173788364b3cccf7398ed682';

        // Migrate hashes to legacy HMAC to enforce re-migration using HmacHashMigration
        array_walk_recursive($parameters, static function (mixed &$value, string|int $key) use ($parametersToMigrate): void {
            if (!is_string($value) || !in_array($key, $parametersToMigrate, true)) {
                return;
            }

            $stringWithoutHmac = substr($value, 0, -40);
            $hmac = Core\Utility\GeneralUtility::hmac($stringWithoutHmac);
            $value = $stringWithoutHmac . $hmac;
        });

        $legacyRequestParameters = Src\Type\JsonType::fromArray($parameters);

        $I->updateInDatabase(
            Src\Domain\Model\Consent::TABLE_NAME,
            ['original_request_parameters' => (string)$legacyRequestParameters],
            ['validation_hash' => $hash],
        );

        $I->amOnPage($this->approveUrl);

        $I->see('Thanks for your consent.');
    }

    public function canDismissConsentAndInvokeConfirmationFinisher(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::CONFIRMATION_AFTER_DISMISS);

        $I->amOnPage($this->dismissUrl);

        $I->see('Your consent was dismissed.');
    }

    public function canDismissConsentAndInvokeRedirectFinisher(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::REDIRECT_AFTER_DISMISS);

        $I->amOnPage($this->dismissUrl);

        $I->seeCurrentUrlEquals('/');
    }

    public function canDismissConsentAfterApprovalAndInvokeRedirectFinisher(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::REDIRECT_AFTER_APPROVE_AND_DISMISS);

        $I->amOnPage($this->approveUrl);

        $I->seeCurrentUrlEquals('/');

        $I->amOnPage($this->dismissUrl);

        $I->seeCurrentUrlEquals('/');
    }

    public function canApproveConsentWithAdditionalVerificationStep(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::VERIFY);

        $I->amOnPage($this->approveUrl);
        $I->see('Verify approval');

        $I->click('Approve');
        $I->see('Consent successful');
    }

    public function canDismissConsentWithAdditionalVerificationStep(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::VERIFY);

        $I->amOnPage($this->dismissUrl);
        $I->see('Verify dismissal');

        $I->click('Dismiss');
        $I->see('Consent successfully revoked');
    }

    public function canPreviewValidationPluginWithActiveBackendSession(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->amOnPage('/confirmation');
        $I->see('RequiredArgumentMissingException');

        $I->loginAs('admin');

        $I->amOnPage('/confirmation');
        $I->see('This is a preview of the form_consent validation plugin without any functionality.');
    }

    public function cannotApproveOrDismissAlreadyDismissedConsent(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I);

        $I->amOnPage($this->dismissUrl);

        $I->amOnPage($this->approveUrl);
        $I->see('The link you clicked is no longer valid or has already been clicked. Please fill out the form again.');

        $I->amOnPage($this->dismissUrl);
        $I->see('The link you clicked is no longer valid or has already been clicked.');
    }

    public function cannotApproveOrDismissConsentIfEmailAddressIsInvalid(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I);

        $queryParams = $I->extractQueryParametersFromUrl($this->dismissUrl);
        $hash = $queryParams['tx_formconsent_consent']['hash'] ?? null;

        $I->updateInDatabase(
            Src\Domain\Model\Consent::TABLE_NAME,
            ['email' => 'foo'],
            ['validation_hash' => $hash]
        );

        $I->amOnPage($this->approveUrl);
        $I->see('The email address sent with the link is not valid. Please fill out the form again.');

        $I->amOnPage($this->dismissUrl);
        $I->see('The email address sent with the link is not valid. Please fill out the form again.');
    }

    public function cannotApproveAlreadyApprovedConsent(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I);

        $I->amOnPage($this->approveUrl);

        $I->amOnPage($this->approveUrl);
        $I->see('The link you clicked has already been clicked. This means that consent has already been given and the link is no longer valid.');
    }

    public function seeErrorOnFinisherExceptionDuringConsentApproval(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::INVALID_CLOSURE_AFTER_APPROVE);

        $I->amOnPage($this->approveUrl);
        $I->see('An unexpected error occurred while processing the consent. Please try again or contact the webmaster of this website with the following error code: 1332155239');
    }

    public function seeErrorOnFinisherExceptionDuringConsentDismissal(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Tests\Acceptance\Support\Helper\Form::INVALID_CLOSURE_AFTER_DISMISS);

        $I->amOnPage($this->dismissUrl);
        $I->see('An unexpected error occurred while processing the consent. Please try again or contact the webmaster of this website with the following error code: 1332155239');
    }

    private function submitFormAndExtractUrls(
        Tests\Acceptance\Support\AcceptanceTester $I,
        string $form = Tests\Acceptance\Support\Helper\Form::DEFAULT,
        bool $attachFile = false,
    ): void {
        $I->amOnPage('/');
        $I->fillAndSubmitForm($form, $attachFile);

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $this->approveUrl = $I->grabUrlFromEmailBody(0);
        $this->dismissUrl = $I->grabUrlFromEmailBody(1);
    }
}
