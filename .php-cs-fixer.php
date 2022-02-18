<?php

declare(strict_types=1);

$header = <<<EOM
This file is part of the TYPO3 CMS extension "form_consent".

Copyright (C) %d Elias Häußler <elias@haeussler.dev>
EOM;

$config = \TYPO3\CodingStandards\CsFixerConfig::create()
    ->setHeader(sprintf($header, \date('Y')))
    ->addRules([
        'native_function_invocation' => true,
    ]);

$finder = $config->getFinder()
    ->in(__DIR__)
    ->ignoreVCSIgnored(true);

return $config;
