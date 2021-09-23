<?php

declare(strict_types=1);

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->setCacheFile('.php-cs-fixer.cache');
$finder = $config->getFinder()
    ->in(__DIR__)
    ->ignoreVCSIgnored(true);
return $config;
