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

namespace EliasHaeussler\Typo3FormConsent\Form\Element;

use EliasHaeussler\Typo3FormConsent\Configuration\Localization;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * ConsentDataElement
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class ConsentDataElement extends AbstractFormElement
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

    protected function renderRecordDataHtml(): string
    {
        $row = $this->data['databaseRow'] ?? [];
        $formData = (string)$row['data'];
        $formData = json_decode($formData, true) ?: [];
        $title = Localization::forBackendForm('data.header', true);

        if ($formData === []) {
            return $this->renderAlert('noDataAvailable');
        }

        return DebuggerUtility::var_dump($formData, $title, 8, false, false, true);
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    protected function renderFormElement(array $result, string $elementHtml): array
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

    protected function renderAlert(string $localizationKey): string
    {
        $html[] = '<div class="alert alert-info alert-message" role="alert">';
        $html[] =   Localization::forBackendForm('message.' . $localizationKey, true);
        $html[] = '</div>';

        return implode(PHP_EOL, $html);
    }
}
