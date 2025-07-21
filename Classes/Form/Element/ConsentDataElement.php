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

namespace EliasHaeussler\Typo3FormConsent\Form\Element;

use EliasHaeussler\Typo3FormConsent\Configuration;
use TYPO3\CMS\Backend;
use TYPO3\CMS\Extbase;

/**
 * ConsentDataElement
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
final class ConsentDataElement extends Backend\Form\Element\AbstractFormElement
{
    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        $result = $this->initializeResultArray();

        if ($this->data['command'] === 'new') {
            $elementHtml = $this->renderAlert('newRecord');
        } else {
            $elementHtml = $this->renderRecordDataHtml();
        }

        return $this->renderFormElement($result, $elementHtml);
    }

    private function renderRecordDataHtml(): string
    {
        $row = $this->data['databaseRow'] ?? [];
        $formData = (string)$row['data'];
        $formData = json_decode($formData, true) ?? [];
        $title = Configuration\Localization::forBackendForm('data.header', true);

        if (!\is_array($formData) || $formData === []) {
            return $this->renderAlert('noDataAvailable');
        }

        return Extbase\Utility\DebuggerUtility::var_dump($formData, $title, 8, false, false, true);
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function renderFormElement(array $result, string $elementHtml): array
    {
        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $result = $this->mergeChildReturnIntoExistingResult($result, $fieldInformationResult, false);

        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] =   $fieldInformationHtml;
        $html[] =   '<div class="form-wizards-wrap">';
        $html[] =     '<div class="form-wizards-element">';
        $html[] =       $elementHtml;
        $html[] =     '</div>';
        $html[] =   '</div>';
        $html[] = '</div>';

        $result['html'] = implode(PHP_EOL, $html);

        return $result;
    }

    private function renderAlert(string $localizationKey): string
    {
        $html = [];
        $html[] = '<div class="alert alert-info alert-message" role="alert">';
        $html[] =   Configuration\Localization::forBackendForm('message.' . $localizationKey, true);
        $html[] = '</div>';

        return implode(PHP_EOL, $html);
    }
}
