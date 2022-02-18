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

\EliasHaeussler\Typo3FormConsent\Configuration\Extension::registerFormEngineNode();
\EliasHaeussler\Typo3FormConsent\Configuration\Extension::registerPageTsConfig();
\EliasHaeussler\Typo3FormConsent\Configuration\Extension::registerPlugin();
\EliasHaeussler\Typo3FormConsent\Configuration\Extension::registerIcons();
\EliasHaeussler\Typo3FormConsent\Configuration\Extension::registerGarbageCollectionTask();
