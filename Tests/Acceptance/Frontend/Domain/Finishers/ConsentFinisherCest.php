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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Frontend\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent as Src;
use EliasHaeussler\Typo3FormConsent\Tests;

/**
 * ConsentFinisherCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentFinisherCest
{
    public function _before(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->amOnPage('/');
    }

    public function canSubmitForm(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $numberOfFormFixtures = $this->getNumberOfFormFixtures($I);

        $I->fillAndSubmitForm(Tests\Acceptance\Support\Helper\Form::DEFAULT, true);

        $I->waitForText('Please approve your consent.', 5);

        $I->seeInDatabase(
            Src\Domain\Model\Consent::TABLE_NAME,
            [
                'data like' => '{"cr-field":"%","email-1":"user@example.com","fileupload-1":' . ($numberOfFormFixtures + 1) . '}',
                'email' => 'user@example.com',
                'form_persistence_identifier' => '1:/form_definitions/contact.form.yaml',
                'original_content_element_uid' => 1,
            ]
        );
    }

    public function canUseLastInsertedConsentFromFinisherVariableProvider(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $numberOfFormFixtures = $this->getNumberOfFormFixtures($I);

        $I->fillAndSubmitForm(Tests\Acceptance\Support\Helper\Form::DEFAULT, true);

        $I->waitForText('Please approve your consent.', 5);

        $uid = $I->grabFromDatabase(
            Src\Domain\Model\Consent::TABLE_NAME,
            'uid',
            [
                'data like' => '{"cr-field":"%","email-1":"user@example.com","fileupload-1":' . ($numberOfFormFixtures + 1) . '}',
                'email' => 'user@example.com',
                'form_persistence_identifier' => '1:/form_definitions/contact.form.yaml',
                'original_content_element_uid' => 1,
            ]
        );

        $I->seeInDatabase(
            'fe_users',
            [
                'username' => 'consent ' . $uid,
                'email' => 'user@example.com',
                'image' => $numberOfFormFixtures + 1,
            ]
        );
    }

    public function receiveConsentMail(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm();

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');

        $I->haveNumberOfUnreadEmails(1);
        $I->openNextUnreadEmail();
        $I->seeInOpenedEmailSubject('Approve your consent');
    }

    public function seeApproveLinkInConsentMail(Tests\Acceptance\Support\AcceptanceTester $I): void
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

    public function seeDismissLinkInConsentMail(Tests\Acceptance\Support\AcceptanceTester $I): void
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

    public function canSubmitFormAndStoreUploadedFiles(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm(Tests\Acceptance\Support\Helper\Form::EMAIL_AFTER_APPROVE, true);

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $I->seeUrlsInEmailBody(2);

        $approveUrl = $I->grabUrlFromEmailBody(0);

        $I->amOnPage($approveUrl);

        $I->fetchEmails();
        $I->accessInboxFor('admin@example.com');
        $I->openNextUnreadEmail();

        $I->haveNumberOfAttachmentsInOpenedEmail(1);

        $I->openNextAttachmentInOpenedEmail();

        $I->seeInFilenameOfOpenedAttachment('dummy.png');
    }

    public function canSubmitFormWithCustomSenderAndReplyToRecipient(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm(Tests\Acceptance\Support\Helper\Form::V2);

        $I->fetchEmails();
        $I->accessInboxFor('user@example.com');
        $I->openNextUnreadEmail();

        $I->seeInOpenedEmailSender('sender@example.com');
        $I->seeInOpenedEmailReplyTo('reply-to@example.com');
    }

    public function cannotSubmitInvalidForm(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->fillAndSubmitForm(Tests\Acceptance\Support\Helper\Form::INVALID);

        $I->waitForText('The finisher option "recipientAddress" must be set.', 5);
    }

    private function getNumberOfFormFixtures(Tests\Acceptance\Support\AcceptanceTester $I): int
    {
        $fixtureFiles = glob(\dirname(__DIR__, 3) . '/Data/Fileadmin/form_definitions/*.form.yaml');

        if ($fixtureFiles === false) {
            $I->fail('Unable to determine number of form fixtures.');

            // Actually, this is superfluous as $I->fail() exists and will never
            // reach this return. However, since PHPStan does not (yet) understand
            // Codeception's internal logic, we must teach it ourselves.
            return 0;
        }

        return \count($fixtureFiles);
    }
}
