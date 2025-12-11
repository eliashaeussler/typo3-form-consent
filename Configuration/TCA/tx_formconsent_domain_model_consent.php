<?php

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

$tableName = \EliasHaeussler\Typo3FormConsent\Domain\Model\Consent::TABLE_NAME;

return [
    'ctrl' => [
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'title' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent',
        'delete' => 'deleted',
        'iconfile' => 'EXT:form_consent/Resources/Public/Icons/tx_formconsent_domain_model_consent.svg',
        'searchFields' => 'email, data, form_persistence_identifier, validation_hash',
        'default_sortby' => 'date DESC',
    ],
    'columns' => [
        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'softref' => 'email[subst]',
                'readOnly' => true,
                'required' => true,
            ],
        ],
        'date' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.date',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
                'readOnly' => true,
                'required' => true,
            ],
        ],
        'data' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.data',
            'config' => [
                'type' => 'user',
                'renderType' => 'consentData',
                'readOnly' => true,
            ],
        ],
        'form_persistence_identifier' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.form_persistence_identifier',
            'config' => [
                'type' => 'input',
                'softref' => 'formPersistenceIdentifier',
                'readOnly' => true,
                'required' => true,
            ],
        ],
        'original_request_parameters' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.original_request_parameters',
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'original_content_element_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.original_content_element_uid',
            'config' => [
                'type' => 'group',
                'allowed' => 'tt_content',
                'foreign_table' => 'tt_content',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'readOnly' => true,
            ],
        ],
        'state' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.state',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::New->label(),
                        'value' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::New->value,
                        'icon' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::New->icon(),
                    ],
                    [
                        'label' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::Approved->label(),
                        'value' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::Approved->value,
                        'icon' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::Approved->icon(),
                    ],
                    [
                        'label' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::Dismissed->label(),
                        'value' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::Dismissed->value,
                        'icon' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::Dismissed->icon(),
                    ],
                ],
                'default' => \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::New->value,
                'readOnly' => true,
            ],
        ],
        'update_date' => [
            'exclude' => true,
            'displayCond' => 'FIELD:state:>:' . \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::New->value,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.update_date',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'valid_until' => [
            'exclude' => true,
            'displayCond' => 'FIELD:state:=:' . \EliasHaeussler\Typo3FormConsent\Enums\ConsentState::New->value,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.valid_until',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'validation_hash' => [
            'exclude' => true,
            'label' => 'LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tx_formconsent_domain_model_consent.validation_hash',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    email,
                    date,
                    data,
                    form_persistence_identifier,
                    original_content_element_uid,
                --div--;LLL:EXT:form_consent/Resources/Private/Language/locallang_db.xlf:tabs.consent,
                    state,
                    update_date,
                    valid_until,
                    validation_hash
            ',
        ],
    ],
];
