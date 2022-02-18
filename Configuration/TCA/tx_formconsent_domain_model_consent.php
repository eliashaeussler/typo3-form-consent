<?php

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
        'approved' => [
            'exclude' => true,
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('approved', $tableName),
            'config' => [
                'type' => 'check',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'valid_until' => [
            'exclude' => true,
            'displayCond' => 'FIELD:approved:=:0',
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('valid_until', $tableName),
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'approval_date' => [
            'exclude' => true,
            'displayCond' => 'FIELD:approved:>:0',
            'label' => \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forField('approval_date', $tableName),
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
                --div--;' . \EliasHaeussler\Typo3FormConsent\Configuration\Localization::forTab('consent') . ',
                    approved,
                    approval_date,
                    valid_until,
                    validation_hash
            ',
        ],
    ],
];
