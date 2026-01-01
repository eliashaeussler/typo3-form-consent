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

namespace EliasHaeussler\Typo3FormConsent\Tests\Acceptance\Backend\Widget;

use EliasHaeussler\Typo3CodeceptionHelper;
use EliasHaeussler\Typo3FormConsent as Src;
use EliasHaeussler\Typo3FormConsent\Tests;

/**
 * ApprovedConsentsWidgetCest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final readonly class ApprovedConsentsWidgetCest
{
    public function _before(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->truncateTable('be_dashboards');
    }

    public function canAddANewWidget(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $dialog,
    ): void {
        $this->logInToBackend($I);
        $this->addWidget($I, $dialog);
    }

    public function canSeeDoughnutChartWithConsents(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $dialog,
    ): void {
        $I->truncateTable(Src\Domain\Model\Consent::TABLE_NAME);

        $this->createConsents($I);
        $this->logInToBackend($I);
        $this->addWidget($I, $dialog);

        $canvasSelector = Tests\Acceptance\Support\Enums\Selectors::DashboardWidgetCanvas;

        $I->waitForElementVisible($canvasSelector);

        $data = $I->executeJS(<<<JS
return await import('@typo3/dashboard/contrib/chartjs.js').then((chartjs) => {
    const node = document.querySelector('$canvasSelector');
    const chart = chartjs.Chart.getChart(node);

    return chart.config.data.datasets[0].data;
});
JS);

        /* @see Src\Widget\Provider\ConsentChartDataProvider::getChartData() */
        $expected = [
            2, // approved
            4, // non-approved
            3, // dismissed
        ];

        $I->assertSame($expected, $data);
    }

    private function addWidget(
        Tests\Acceptance\Support\AcceptanceTester $I,
        Tests\Acceptance\Support\Helper\ModalDialog $dialog,
    ): void {
        $I->waitForElementClickable(Tests\Acceptance\Support\Enums\Selectors::DashboardAddWidgetButton);
        $I->click(Tests\Acceptance\Support\Enums\Selectors::DashboardAddWidgetButton);

        $dialog->canSeeDialog();

        $I->executeJS(
            sprintf(
                'document.querySelector(\'%s typo3-backend-new-record-wizard\').shadowRoot.querySelector(\'%s\').click()',
                Tests\Acceptance\Support\Helper\ModalDialog::$openedModalSelector,
                Tests\Acceptance\Support\Enums\Selectors::DashboardModalItemTitle,
            ),
        );
        $I->switchToIFrame(Typo3CodeceptionHelper\Enums\Selectors::BackendContentFrame);
        $I->waitForElementVisible(Tests\Acceptance\Support\Enums\Selectors::DashboardWidgetCanvas);
        $I->see('Form consent', Tests\Acceptance\Support\Enums\Selectors::DashboardWidget);
    }

    private function createConsents(Tests\Acceptance\Support\AcceptanceTester $I): void
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

    private function logInToBackend(Tests\Acceptance\Support\AcceptanceTester $I): void
    {
        $I->loginAs('admin');
        $I->openModule(Tests\Acceptance\Support\Enums\Selectors::DashboardModule);
    }
}
