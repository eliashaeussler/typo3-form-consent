<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Frontend\Controller;

use EliasHaeussler\Typo3FormConsent as Src;
use EliasHaeussler\Typo3FormConsent\Tests;

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

    public function _before(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I);
    }

    public function canApproveConsentViaMail(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $queryParams = $I->extractQueryParametersFromUrl($this->approveUrl);
        $hash = $queryParams['tx_formconsent_consent']['hash'] ?? null;

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
                'valid_until' => null,
            ]
        );
    }

    public function canDismissConsentViaMail(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
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

    public function cannotApproveOrDismissAlreadyDismissedConsent(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->amOnPage($this->dismissUrl);

        $I->amOnPage($this->approveUrl);
        $I->see('The link you clicked is no longer valid or has already been clicked. Please fill out the form again.');

        $I->amOnPage($this->dismissUrl);
        $I->see('The link you clicked is no longer valid or has already been clicked.');
    }

    public function cannotApproveOrDismissConsentIfEmailAddressIsInvalid(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
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
    ): void {
        $I->amOnPage('/');
        $I->fillAndSubmitForm($form);

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $this->approveUrl = $I->grabUrlFromEmailBody(0);
        $this->dismissUrl = $I->grabUrlFromEmailBody(1);
    }
}
