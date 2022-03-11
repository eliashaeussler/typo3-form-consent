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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Frontend;

use EliasHaeussler\Typo3FormConsent\Domain\Model\Consent;
use EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\AcceptanceTester;
use EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Helper\Form;

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

    public function _before(AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I);
    }

    public function canApproveConsentViaMail(AcceptanceTester $I): void
    {
        ['tx_formconsent_consent' => ['hash' => $hash]] = $I->extractQueryParametersFromUrl($this->approveUrl);

        $I->amOnPage($this->approveUrl);

        $I->see('Consent successful');

        $I->seeInDatabase(
            Consent::TABLE_NAME,
            [
                'data' => '{"email-1":"user@example.com","fileupload-1":null}',
                'email' => 'user@example.com',
                'approved' => '1',
                'form_persistence_identifier' => '1:/form_definitions/contact.form.yaml',
                'original_content_element_uid' => 1,
                'original_request_parameters' => null,
                'validation_hash' => $hash,
                'valid_until' => null,
            ]
        );
    }

    public function canDismissConsentViaMail(AcceptanceTester $I): void
    {
        ['tx_formconsent_consent' => ['hash' => $hash]] = $I->extractQueryParametersFromUrl($this->dismissUrl);

        $I->amOnPage($this->dismissUrl);

        $I->see('Consent successfully revoked');

        $I->seeInDatabase(
            Consent::TABLE_NAME,
            [
                'deleted' => '1',
                'data' => null,
                'email' => 'user@example.com',
                'approved' => '0',
                'form_persistence_identifier' => '1:/form_definitions/contact.form.yaml',
                'original_content_element_uid' => 1,
                'original_request_parameters' => null,
                'validation_hash' => $hash,
            ]
        );
    }

    public function canApproveConsentAndInvokeConfirmationFinisher(AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Form::CONFIRMATION_AFTER_APPROVE);

        $I->amOnPage($this->approveUrl);

        $I->see('Thanks for your consent.');
    }

    public function canApproveConsentAndInvokeEmailFinisher(AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Form::EMAIL_AFTER_APPROVE);

        $I->amOnPage($this->approveUrl);

        $I->fetchEmails();
        $I->accessInboxFor('admin@example.com');

        $I->haveNumberOfUnreadEmails(1);
        $I->openNextUnreadEmail();
        $I->seeInOpenedEmailSubject('Consent approved');
    }

    public function canApproveConsentAndInvokeRedirectFinisher(AcceptanceTester $I): void
    {
        $this->submitFormAndExtractUrls($I, Form::REDIRECT_AFTER_APPROVE);

        $I->amOnPage($this->approveUrl);

        $I->seeCurrentUrlEquals('/');
    }

    public function cannotApproveOrDismissAlreadyDismissedConsent(AcceptanceTester $I): void
    {
        $I->amOnPage($this->dismissUrl);

        $I->amOnPage($this->approveUrl);
        $I->see('The link you clicked is no longer valid or has already been clicked. Please fill out the form again.');

        $I->amOnPage($this->dismissUrl);
        $I->see('The link you clicked is no longer valid or has already been clicked.');
    }

    public function cannotApproveOrDismissConsentIfEmailAddressIsInvalid(AcceptanceTester $I): void
    {
        ['tx_formconsent_consent' => ['hash' => $hash]] = $I->extractQueryParametersFromUrl($this->dismissUrl);

        $I->updateInDatabase(
            Consent::TABLE_NAME,
            ['email' => 'foo'],
            ['validation_hash' => $hash]
        );

        $I->amOnPage($this->approveUrl);
        $I->see('The email address sent with the link is not valid. Please fill out the form again.');

        $I->amOnPage($this->dismissUrl);
        $I->see('The email address sent with the link is not valid. Please fill out the form again.');
    }

    public function cannotApproveAlreadyApprovedConsent(AcceptanceTester $I): void
    {
        $I->amOnPage($this->approveUrl);

        $I->amOnPage($this->approveUrl);
        $I->see('The link you clicked has already been clicked. This means that consent has already been given and the link is no longer valid.');
    }

    private function submitFormAndExtractUrls(AcceptanceTester $I, string $form = Form::DEFAULT): void
    {
        $I->amOnPage('/');
        $I->fillAndSubmitForm($form);

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $this->approveUrl = $I->grabUrlFromEmailBody(0);
        $this->dismissUrl = $I->grabUrlFromEmailBody(1);
    }
}
