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

/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Form consent',
    'description' => 'An extension for TYPO3 CMS that adds double opt-in functionality to EXT:form. It allows the dynamic adaptation of the entire double opt-in process using various events. In addition, the extension integrates seamlessly into TYPO3, for example to delete outdated consents in compliance with the GDPR.',
    'category' => 'fe',
    'author' => 'Elias Häußler',
    'author_email' => 'elias@haeussler.dev',
    'state' => 'alpha',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => false,
    'version' => '0.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
];
