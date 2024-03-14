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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Domain\Finishers;

use EliasHaeussler\Typo3FormConsent as Src;
use PHPUnit\Framework;
use Symfony\Component\EventDispatcher;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * ConsentFinisherTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Domain\Finishers\ConsentFinisher::class)]
final class ConsentFinisherTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    protected array $pathsToProvideInTestInstance = [
        'typo3conf/ext/form_consent/Tests/Acceptance/Data/Fileadmin/form_definitions' => 'fileadmin/form_definitions',
    ];

    protected array $additionalFoldersToCreate = [
        'fileadmin/user_upload',
    ];

    private EventDispatcher\EventDispatcher $eventDispatcher;
    private Src\Domain\Repository\ConsentRepository $consentRepository;
    private Src\Domain\Finishers\ConsentFinisher $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = new EventDispatcher\EventDispatcher();
        $this->consentRepository = $this->get(Src\Domain\Repository\ConsentRepository::class);
        $this->subject = new Src\Domain\Finishers\ConsentFinisher(
            new Src\Domain\Factory\ConsentFactory(
                $this->get(Extbase\Configuration\ConfigurationManagerInterface::class),
                $this->get(Core\Context\Context::class),
                $this->eventDispatcher,
                $this->get(Src\Type\Transformer\FormRequestTypeTransformer::class),
                $this->get(Src\Type\Transformer\FormValuesTypeTransformer::class),
                $this->get(Src\Service\HashService::class),
            ),
            $this->eventDispatcher,
            $this->get(Core\Mail\Mailer::class),
            $this->get(Extbase\Persistence\PersistenceManagerInterface::class),
        );

        $this->subject->setFinisherIdentifier('Consent');
        $this->subject->setOptions([
            'subject' => '',
            'recipientAddress' => 'foo@baz.com',
            'recipientName' => '',
            'senderAddress' => '',
            'senderName' => '',
            'approvalPeriod' => 86400,
            'showDismissLink' => true,
            'confirmationPid' => 1,
            'storagePid' => '',
        ]);

        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/be_users.csv');
        $this->importCSVDataSet(\dirname(__DIR__, 2) . '/Fixtures/pages.csv');

        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(Core\Localization\LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    #[Framework\Attributes\Test]
    public function executeDoesNothingIfFinisherInvocationIsCancelled(): void
    {
        $eventDispatched = false;

        $this->eventDispatcher->addListener(
            Src\Event\ModifyConsentEvent::class,
            static function (Src\Event\ModifyConsentEvent $event) use (&$eventDispatched) {
                $eventDispatched = true;
                $event->getFinisherContext()->cancel();
            }
        );

        $finisherContext = $this->createFinisherContext();

        $this->subject->execute($finisherContext);

        self::assertTrue($eventDispatched);
        self::assertSame([], $this->consentRepository->findAll()->toArray());
    }

    private function createFinisherContext(): Form\Domain\Finishers\FinisherContext
    {
        // Create and initialize TSFE
        $typoScriptFrontendController = $GLOBALS['TSFE'] = $this->createMock(Frontend\Controller\TypoScriptFrontendController::class);
        $typoScriptFrontendController->method('sL')->willReturn('dummy');
        $typoScriptFrontendController->id = 1;
        $typoScriptFrontendController->fe_user = new Frontend\Authentication\FrontendUserAuthentication();
        $typoScriptFrontendController->fe_user->initializeUserSessionManager();

        // Create basic request
        $request = new Core\Http\ServerRequest();
        $request = $request->withAttribute('extbase', new Extbase\Mvc\ExtbaseRequestParameters());
        $request = $request->withAttribute('applicationType', Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $extbaseRequest = new Extbase\Mvc\Request($request);

        // Load form and build form runtime
        $formFactory = $this->get(Form\Domain\Factory\FormFactoryInterface::class);
        $formPersistenceManager = $this->get(Form\Mvc\Persistence\FormPersistenceManagerInterface::class);
        $formDefinitionArray = $formPersistenceManager->load('1:form_definitions/contact.form.yaml');
        $formDefinition = $formFactory->build($formDefinitionArray);
        $formRuntime = $formDefinition->bind($extbaseRequest);

        $constructorArguments = [
            $formRuntime,
        ];

        // @todo Remove once support for TYPO3 v11 is dropped
        if ((new Core\Information\Typo3Version())->getMajorVersion() < 12) {
            $constructorArguments[] = new Extbase\Mvc\Controller\ControllerContext();
        }

        $constructorArguments[] = $extbaseRequest;

        return new Form\Domain\Finishers\FinisherContext(...$constructorArguments);
    }
}
