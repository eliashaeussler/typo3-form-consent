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

namespace EliasHaeussler\Typo3FormConsent\Event\Listener;

use Derhansen\FormCrshield;
use EliasHaeussler\Typo3FormConsent\Compatibility;
use EliasHaeussler\Typo3FormConsent\Domain;
use EliasHaeussler\Typo3FormConsent\Event;
use EliasHaeussler\Typo3FormConsent\Type;
use Psr\Http\Message;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Form;
use TYPO3\CMS\Frontend;

/**
 * InvokeFinishersListener
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class InvokeFinishersListener
{
    private readonly Core\Information\Typo3Version $typo3Version;

    public function __construct(
        private readonly Extbase\Configuration\ConfigurationManagerInterface $extbaseConfigurationManager,
        private readonly Form\Mvc\Configuration\ConfigurationManagerInterface $formConfigurationManager,
        private readonly Form\Mvc\Persistence\FormPersistenceManagerInterface $formPersistenceManager,
        private readonly Core\Domain\Repository\PageRepository $pageRepository,
    ) {
        $this->typo3Version = new Core\Information\Typo3Version();
    }

    // @todo Enable attribute once support for TYPO3 v12 is dropped
    // #[Core\Attribute\AsEventListener('formConsentInvokeFinishersOnConsentApproveListener')]
    public function onConsentApprove(Event\ApproveConsentEvent $event): void
    {
        $response = $this->invokeFinishers($event->getConsent(), 'isConsentApproved()');
        $event->setResponse($response);
    }

    // @todo Enable attribute once support for TYPO3 v12 is dropped
    // #[Core\Attribute\AsEventListener('formConsentInvokeFinishersOnConsentDismissListener')]
    public function onConsentDismiss(Event\DismissConsentEvent $event): void
    {
        $response = $this->invokeFinishers($event->getConsent(), 'isConsentDismissed()');
        $event->setResponse($response);
    }

    private function invokeFinishers(Domain\Model\Consent $consent, string $condition): ?Message\ResponseInterface
    {
        // Early return if original request is missing
        // or no finisher variants are configured
        if (
            $consent->getOriginalRequestParameters() === null
            || $consent->getOriginalContentElementUid() === 0
            || !$this->areFinisherVariantsConfigured($consent->getFormPersistenceIdentifier(), $condition)
        ) {
            return null;
        }

        // Migrate legacy HMAC hashes after upgrade to TYPO3 v13
        $consent->setOriginalRequestParameters(
            $this->migrateOriginalRequestParameters($consent->getOriginalRequestParameters()),
        );

        // Re-render form to invoke finishers
        $request = $this->createRequestFromOriginalRequestParameters($consent->getOriginalRequestParameters());

        return $this->dispatchFormReRendering($consent, $request);
    }

    private function dispatchFormReRendering(
        Domain\Model\Consent $consent,
        Message\ServerRequestInterface $serverRequest,
    ): ?Message\ResponseInterface {
        // Fetch record of original content element
        $contentElementRecord = $this->fetchOriginalContentElementRecord($consent->getOriginalContentElementUid());

        // Early return if content element record cannot be resolved
        if (!\is_array($contentElementRecord)) {
            return null;
        }

        // Build extbase bootstrap object
        $contentObjectRenderer = Core\Utility\GeneralUtility::makeInstance(Frontend\ContentObject\ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest($serverRequest);
        $contentObjectRenderer->start($contentElementRecord, 'tt_content');
        $contentObjectRenderer->setUserObjectType(Frontend\ContentObject\ContentObjectRenderer::OBJECTTYPE_USER_INT);
        $bootstrap = Core\Utility\GeneralUtility::makeInstance(Extbase\Core\Bootstrap::class);
        $bootstrap->setContentObjectRenderer($contentObjectRenderer);

        // Inject content object renderer
        $serverRequest = $serverRequest->withAttribute('currentContentObject', $contentObjectRenderer);

        $configuration = [
            'extensionName' => 'Form',
            'pluginName' => 'Formframework',
        ];

        // Prepare clean environment
        $globalsBackup = $GLOBALS;
        $this->disableThirdPartyHooks();

        try {
            // Dispatch extbase request
            $content = $bootstrap->run('', $configuration, $serverRequest);
            $response = new Core\Http\Response();
            $response->getBody()->write($content);

            return $response;
        } catch (Core\Http\ImmediateResponseException|Core\Http\PropagateResponseException $exception) {
            // If any immediate response is thrown, use this for further processing
            return $exception->getResponse();
        } finally {
            // Restore previous environment
            foreach ($globalsBackup as $key => $value) {
                $GLOBALS[$key] = $value;
            }
        }
    }

    /**
     * @param Type\JsonType<string, array<string, array<string, mixed>>> $originalRequestParameters
     * @return Type\JsonType<string, array<string, array<string, mixed>>>
     */
    private function migrateOriginalRequestParameters(Type\JsonType $originalRequestParameters): Type\JsonType
    {
        // @todo Remove once support for TYPO3 v12 is dropped
        if ($this->typo3Version->getMajorVersion() < 13) {
            return $originalRequestParameters;
        }

        $migration = new Compatibility\Migration\HmacHashMigration();
        $parameters = $originalRequestParameters->toArray();

        array_walk_recursive($parameters, static function (mixed &$value, string|int $key) use ($migration): void {
            if (!is_string($value) || !is_string($key)) {
                return;
            }

            // Migrate EXT:extbase and EXT:form hash scopes
            $hashScope = Form\Security\HashScope::tryFrom($key);
            $hashScope ??= Extbase\Security\HashScope::tryFrom($key);

            if ($hashScope !== null) {
                $value = $migration->migrate($value, $hashScope->prefix());
            }
        });

        return Type\JsonType::fromArray($parameters);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchOriginalContentElementRecord(int $contentElementUid): ?array
    {
        // Early return if content element UID cannot be  determined
        if ($contentElementUid === 0) {
            return null;
        }

        // Fetch content element record
        $record = $this->pageRepository->checkRecord('tt_content', $contentElementUid);

        // Early return if content element record cannot be resolved
        if (!\is_array($record)) {
            return null;
        }

        return $this->pageRepository->getLanguageOverlay('tt_content', $record);
    }

    /**
     * @param Type\JsonType<string, array<string, array<string, mixed>>> $originalRequestParameters
     */
    private function createRequestFromOriginalRequestParameters(Type\JsonType $originalRequestParameters): Message\ServerRequestInterface
    {
        return $this->getServerRequest()
            ->withMethod('POST')
            ->withParsedBody($originalRequestParameters->toArray());
    }

    private function disableThirdPartyHooks(): void
    {
        // Hooks from EXT:form_crshield must be disabled since they would avoid successful re-rendering
        if (class_exists(FormCrshield\Hooks\Form::class)) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterInitializeCurrentPage'] = array_diff(
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterInitializeCurrentPage'],
                [FormCrshield\Hooks\Form::class],
            );
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'] = array_diff(
                $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'],
                [FormCrshield\Hooks\Form::class],
            );
        }
    }

    private function areFinisherVariantsConfigured(string $formPersistenceIdentifier, string $condition): bool
    {
        if ($this->typo3Version->getMajorVersion() >= 13) {
            $typoScriptSettings = $this->extbaseConfigurationManager->getConfiguration(
                Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'form',
            );
            $formSettings = $this->formConfigurationManager->getYamlConfiguration($typoScriptSettings, true);
            $formConfiguration = $this->formPersistenceManager->load(
                $formPersistenceIdentifier,
                $formSettings,
                $typoScriptSettings,
            );
        } else {
            // @todo Remove once support for TYPO3 v12 is dropped
            $formConfiguration = $this->formPersistenceManager->load($formPersistenceIdentifier);
        }

        foreach ($formConfiguration['variants'] ?? [] as $variant) {
            if (str_contains($variant['condition'] ?? '', $condition) && isset($variant['finishers'])) {
                return true;
            }
        }

        return false;
    }

    private function getServerRequest(): Message\ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? Core\Http\ServerRequestFactory::fromGlobals();
    }
}
