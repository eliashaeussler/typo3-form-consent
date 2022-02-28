<?php

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

/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Form consent',
    'description' => 'An extension for TYPO3 CMS that adds double opt-in functionality to EXT:form. It allows the dynamic adaptation of the entire double opt-in process using various events. In addition, the extension integrates seamlessly into TYPO3, for example to delete outdated consents in compliance with the GDPR.',
    'category' => 'fe',
    'author' => 'Elias Häußler',
    'author_email' => 'elias@haeussler.dev',
    'state' => 'alpha',
    'clearCacheOnLoad' => false,
    'version' => '0.2.2',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
];
