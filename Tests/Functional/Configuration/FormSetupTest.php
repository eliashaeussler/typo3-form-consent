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

namespace EliasHaeussler\Typo3FormConsent\Tests\Functional\Configuration;

use EliasHaeussler\Typo3FormConsent as Src;
use EliasHaeussler\Typo3FormConsent\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Form;

/**
 * FormSetupTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class FormSetupTest extends Tests\Functional\ExtbaseRequestAwareFunctionalTestCase
{
    /**
     * @var list<string>
     */
    private const EXPECTED_FINISHERS = [
        'EmailToSender',
        'EmailToReceiver',
        'Redirect',
        'DeleteUploads',
        'Confirmation',
    ];

    /**
     * Finishers that cannot be added in the form editor must not provide the
     * editor: EXT:form rejects properties without hmac for those on save.
     *
     * @var list<string>
     */
    private const UNSUPPORTED_FINISHERS = [
        'Closure',
        'FlashMessage',
        'SaveToDatabase',
    ];

    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
        'form',
        'install',
    ];

    protected array $testExtensionsToLoad = [
        'form_consent',
    ];

    #[Framework\Attributes\Test]
    public function consentConditionEditorIsAddedToFinishersCreatableInFormEditor(): void
    {
        $finishersWithEditor = [];

        foreach ($this->getFinisherPropertyCollections() as $finisher) {
            $editor = $this->findConsentConditionEditor($finisher['editors'] ?? []);

            if ($editor === null) {
                continue;
            }

            $finishersWithEditor[] = $finisher['identifier'] ?? '';

            self::assertSame('options.consentCondition', $editor['propertyPath']);
            self::assertSame(
                ['', ...array_column(Src\Enums\ConsentCondition::cases(), 'value')],
                array_column($editor['selectOptions'], 'value'),
            );
            // Editors must state that a condition requires the Consent finisher
            self::assertNotSame('', $editor['description'] ?? '');
        }

        self::assertSame(self::EXPECTED_FINISHERS, $finishersWithEditor);
    }

    #[Framework\Attributes\Test]
    public function consentConditionEditorIsNotAddedToUnsupportedFinishers(): void
    {
        foreach ($this->getFinisherPropertyCollections() as $finisher) {
            $identifier = $finisher['identifier'] ?? '';

            if (!in_array($identifier, ['Consent', ...self::UNSUPPORTED_FINISHERS], true)) {
                continue;
            }

            self::assertNull(
                $this->findConsentConditionEditor($finisher['editors'] ?? []),
                sprintf('Finisher "%s" must not provide the consent condition editor.', $identifier),
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getFinisherPropertyCollections(): array
    {
        $prototypeConfiguration = $this->get(Form\Domain\Configuration\ConfigurationService::class)
            ->getPrototypeConfiguration('standard');

        return array_values(
            $prototypeConfiguration['formElementsDefinition']['Form']['formEditor']['propertyCollections']['finishers'],
        );
    }

    /**
     * @param array<mixed> $editors
     * @return array<string, mixed>|null
     */
    private function findConsentConditionEditor(array $editors): ?array
    {
        foreach ($editors as $editor) {
            if (is_array($editor) && ($editor['identifier'] ?? '') === Src\Enums\ConsentCondition::FINISHER_OPTION) {
                return $editor;
            }
        }

        return null;
    }
}
