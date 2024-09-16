<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional;

use EliasHaeussler\Typo3FormConsent\TestExtension;
use Psr\Http\Message;
use TYPO3\CMS\Extbase;
use TYPO3\TestingFramework;

/**
 * ExtbaseRequestAwareFunctionalTestCase
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
abstract class ExtbaseRequestAwareFunctionalTestCase extends TestingFramework\Core\Functional\FunctionalTestCase
{
    private const REQUIRED_PATHS = [
        'typo3conf/ext/form_consent/Tests/Build/Configuration/sites/' => 'typo3conf/sites/',
    ];

    private const REQUIRED_EXTENSIONS = [
        'test_extension',
    ];

    protected const BASE_FRONTEND_URL = 'https://typo3-ext-form-consent.ddev.site/';

    protected Extbase\Mvc\Request $request;

    protected function setUp(): void
    {
        $this->addRequiredExtensions();

        parent::setUp();

        // Make sure site config is always populated
        $testbase = new TestingFramework\Core\Testbase();
        $testbase->providePathsInTestInstance($this->instancePath, self::REQUIRED_PATHS);

        $this->request = $GLOBALS['TYPO3_REQUEST'] = $this->provideServerRequest();
        $this->prepareExtbaseEnvironment($this->request);
    }

    protected function tearDown(): void
    {
        TestExtension\Middleware\RequestStorageHandler::$request = null;

        parent::tearDown();
    }

    protected function provideServerRequest(): Extbase\Mvc\Request
    {
        // Import data
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/sys_template.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/tt_content.csv');

        $this->executeFrontendSubRequest(
            new TestingFramework\Core\Functional\Framework\Frontend\InternalRequest(
                self::BASE_FRONTEND_URL,
            ),
        );

        $request = TestExtension\Middleware\RequestStorageHandler::$request;

        self::assertInstanceOf(Message\ServerRequestInterface::class, $request);

        $request = $request->withAttribute('extbase', new Extbase\Mvc\ExtbaseRequestParameters());

        return new Extbase\Mvc\Request($request);
    }

    protected function prepareExtbaseEnvironment(Message\ServerRequestInterface $request): void
    {
        $configurationManager = $this->get(Extbase\Configuration\ConfigurationManagerInterface::class);

        self::assertInstanceOf(Extbase\Configuration\ConfigurationManager::class, $configurationManager);

        $configurationManager->setRequest($request);
    }

    private function addRequiredExtensions(): void
    {
        $requiredExtensions = \array_diff(self::REQUIRED_EXTENSIONS, $this->testExtensionsToLoad);

        foreach ($requiredExtensions as $requiredExtension) {
            $this->testExtensionsToLoad[] = $requiredExtension;
        }
    }
}
