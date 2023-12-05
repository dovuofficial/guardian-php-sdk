<?php

use Dovu\GuardianPhpSdk\Service\AccountService;
use Dovu\GuardianPhpSdk\Service\MrvService;
use Dovu\GuardianPhpSdk\Service\PolicyService;
use Dovu\GuardianPhpSdk\Service\StateService;

return [

    'app' => [
        'base_url' => 'http://localhost:3001/api/',
    ],

    'local' => [
        'policy_id' => '655f71d15e7098d59076e819',
        'hmac_secret' => '1234567890',
    ],

    'services' => [
        'accounts' => AccountService::class,
        'policies' => PolicyService::class,
        'mrv' => MrvService::class,
        'state' => StateService::class,
    ],
];

