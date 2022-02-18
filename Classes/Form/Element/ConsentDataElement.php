<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2022 Elias Häußler <elias@haeussler.dev>
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
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
     * @param string $elementHtml
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

        $result['html'] = implode(LF, $html);

        return $result;
    }

    protected function renderAlert(string $localizationKey): string
    {
        $html[] = '<div class="alert alert-info alert-message" role="alert">';
        $html[] =   Localization::forBackendForm('message.' . $localizationKey, true);
        $html[] = '</div>';

        return implode(LF, $html);
    }
}
