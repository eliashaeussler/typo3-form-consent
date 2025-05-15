<?php

use EliasHaeussler\Typo3FormConsent\Middleware\FormVariantMiddleware;

return [
    'backend' => [
        'form-consent/form-variants--middleware' => [
            'target' => FormVariantMiddleware::class
        ],
    ],
];

