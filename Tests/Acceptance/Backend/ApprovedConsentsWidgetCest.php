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

namespace Acceptance\Backend;

use EliasHaeussler\Typo3CodeceptionHelper\Enums\Selectors;
use EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\AcceptanceTester;
use EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Support\Helper\ModalDialog;
use EliasHaeussler\Typo3FormConsent\Widget\Provider\ConsentChartDataProvider;
use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * ApprovedConsentsWidgetCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ApprovedConsentsWidgetCest
{
    private readonly Typo3Version $typo3Version;

    public function __construct()
    {
        $this->typo3Version = new Typo3Version();
    }

    public function canAddANewWidget(AcceptanceTester $I, ModalDialog $dialog): void
    {
        $this->logInToBackend($I);
        $this->addWidget($I, $dialog);
    }

    public function canSeeDoughnutChartWithConsents(AcceptanceTester $I, ModalDialog $dialog): void
    {
        // @todo Remove once support for TYPO3 v11 is dropped
        if ($this->typo3Version->getMajorVersion() < 12) {
            $I->markTestSkipped('Test can be executed on TYPO3 >= 12 only.');
        }

        $this->createConsents($I);
        $this->logInToBackend($I);
        $this->addWidget($I, $dialog);

        $I->waitForElementVisible('.widget-identifier-approvedConsentsWidget canvas');

        $data = $I->executeJS(
            <<<JS
return await import('@typo3/dashboard/contrib/chartjs.js').then((chartjs) => {
    const node = document.querySelector('.widget-identifier-approvedConsentsWidget canvas');
    const chart = chartjs.Chart.getChart(node);

    return chart.config.data.datasets[0].data;
});
JS
        );

        /* @see ConsentChartDataProvider::getChartData() */
        $expected = [
            2, // approved
            4, // non-approved
            3, // dismissed
        ];

        $I->assertSame($expected, $data);
    }

    private function addWidget(AcceptanceTester $I, ModalDialog $dialog): void
    {
        $I->click('.js-dashboard-addWidget');

        $dialog->canSeeDialog();

        $I->see('Form consent', '.dashboard-modal-item-title');
        $I->click('Form consent');
        $I->switchToIFrame(Selectors::BackendContentFrame->value);
        $I->waitForElementVisible('.widget-identifier-approvedConsentsWidget');
    }

    private function createConsents(AcceptanceTester $I): void
    {
        // Approve consents
        for ($i = 0; $i < 2; $i++) {
            $I->amOnPage('/');
            $I->fillAndSubmitForm();

            $I->fetchEmails();
            $I->accessInboxFor('user@example.com');
            $I->openNextUnreadEmail();

            $approveUrl = $I->grabUrlFromEmailBody();

            $I->amOnPage($approveUrl);
        }

        // Dismiss consents
        for ($i = 0; $i < 3; $i++) {
            $I->amOnPage('/');
            $I->fillAndSubmitForm();

            $I->fetchEmails();
            $I->accessInboxFor('user@example.com');
            $I->openNextUnreadEmail();

            $dismissUrl = $I->grabUrlFromEmailBody(1);

            $I->amOnPage($dismissUrl);
        }

        // Don't touch consents
        for ($i = 0; $i < 4; $i++) {
            $I->amOnPage('/');
            $I->fillAndSubmitForm();
        }
    }

    private function logInToBackend(AcceptanceTester $I): void
    {
        if ($this->typo3Version->getMajorVersion() >= 12) {
            $moduleIdentifier = '[data-modulemenu-identifier="dashboard"]';
        } else {
            // @todo Remove once support for TYPO3 v11 is dropped
            $moduleIdentifier = '#dashboard';
        }

        $I->loginAs('admin');
        $I->openModule($moduleIdentifier);
    }
}
