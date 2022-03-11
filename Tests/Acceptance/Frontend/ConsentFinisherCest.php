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
 * ConsentFinisherCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentFinisherCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
    }

    public function canSubmitForm(AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm(Form::DEFAULT, true);

        $I->waitForText('Please approve your consent.', 5);

        $I->seeInDatabase(
            Consent::TABLE_NAME,
            [
                'data' => '{"email-1":"user@example.com","fileupload-1":7}',
                'email' => 'user@example.com',
                'form_persistence_identifier' => '1:/form_definitions/contact.form.yaml',
                'original_content_element_uid' => 1,
            ]
        );
    }

    public function receiveConsentMail(AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm();

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');

        $I->haveNumberOfUnreadEmails(1);
        $I->openNextUnreadEmail();
        $I->seeInOpenedEmailSubject('Approve your consent');
    }

    public function seeApproveLinkInConsentMail(AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm();

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $I->seeUrlsInEmailBody(2);

        $approveUrl = $I->grabUrlFromEmailBody(0);

        $I->assertUrlPathEquals('/confirmation', $approveUrl);
        $I->assertQueryParameterEquals('approve', $approveUrl, 'tx_formconsent_consent/action');
        $I->assertQueryParameterEquals('user@example.com', $approveUrl, 'tx_formconsent_consent/email');
    }

    public function seeDismissLinkInConsentMail(AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm();

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $I->seeUrlsInEmailBody(2);

        $dismissUrl = $I->grabUrlFromEmailBody(1);

        $I->assertUrlPathEquals('/confirmation', $dismissUrl);
        $I->assertQueryParameterEquals('dismiss', $dismissUrl, 'tx_formconsent_consent/action');
        $I->assertQueryParameterEquals('user@example.com', $dismissUrl, 'tx_formconsent_consent/email');
    }

    public function canSubmitFormWithCustomSender(AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm(Form::V2);

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $I->seeInOpenedEmailSender('sender@example.com');
    }

    public function cannotSubmitInvalidForm(AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm(Form::INVALID);

        $I->waitForText('The finisher option "recipientAddress" must be set.', 5);
    }
}
