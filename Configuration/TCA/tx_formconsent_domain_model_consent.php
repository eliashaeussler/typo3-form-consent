<?php

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

$tableName = \EliasHaeussler\Typo3FormConsent\Domain\Model\Consent::TABLE_NAME;

/** @noinspection MissingRenderTypeInspection */
return [
    'ctrl' => [
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'title' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forTable($tableName),
        'delete' => 'deleted',
        'iconfile' => \EliasHaeussler\Typo3FormConsent\Configuration\Icon::forTable($tableName),
        'searchFields' => 'email, data, form_persistence_identifier, validation_hash',
        'default_sortby' => 'date DESC',
    ],
    'columns' => [
        'email' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('email', $tableName),
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
                'softref' => 'email[subst]',
                'readOnly' => true,
            ],
        ],
        'date' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('date', $tableName),
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int,required',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'data' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('data', $tableName),
            'config' => [
                'type' => 'user',
                'renderType' => 'consentData',
                'readOnly' => true,
            ],
        ],
        'form_persistence_identifier' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('form_persistence_identifier', $tableName),
            'config' => [
                'type' => 'input',
                'eval' => 'required',
                'softref' => 'formPersistenceIdentifier',
                'readOnly' => true,
            ],
        ],
        'original_request_parameters' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('original_request_parameters', $tableName),
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'original_content_element_uid' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('original_content_element_uid', $tableName),
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
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
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('state', $tableName),
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('state', $tableName, 'new'),
                        \EliasHaeussler\Typo3FormConsent\Type\ConsentStateType::NEW,
                        'overlay-scheduled',
                    ],
                    [
                        \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('state', $tableName, 'approved'),
                        \EliasHaeussler\Typo3FormConsent\Type\ConsentStateType::APPROVED,
                        'overlay-approved',
                    ],
                    [
                        \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('state', $tableName, 'dismissed'),
                        \EliasHaeussler\Typo3FormConsent\Type\ConsentStateType::DISMISSED,
                        'overlay-readonly',
                    ],
                ],
                'default' => \EliasHaeussler\Typo3FormConsent\Type\ConsentStateType::NEW,
                'readOnly' => true,
            ],
        ],
        'update_date' => [
            'exclude' => true,
            'displayCond' => 'FIELD:state:>:' . \EliasHaeussler\Typo3FormConsent\Type\ConsentStateType::NEW,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('update_date', $tableName),
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'valid_until' => [
            'exclude' => true,
            'displayCond' => 'FIELD:state:=:' . \EliasHaeussler\Typo3FormConsent\Type\ConsentStateType::NEW,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('valid_until', $tableName),
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'validation_hash' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('validation_hash', $tableName),
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
                --div--;' . \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forTab('general', true) . ',
                    email,
                    date,
                    data,
                    form_persistence_identifier,
                    original_content_element_uid,
                --div--;' . \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forTab('consent') . ',
                    state,
                    update_date,
                    valid_until,
                    validation_hash
            ',
        ],
    ],
];
