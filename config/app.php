<?php

use Dovu\GuardianPhpSdk\Service\AccountService;
use Dovu\GuardianPhpSdk\Service\MrvService;
use Dovu\GuardianPhpSdk\Service\PolicyService;

return [

    'app' => [
        'base_url' => 'http://localhost:3001/api/',

    ],

    'local' => [
        'policy_id' => '62fa6d888e60e83b2fe05c61',
        'hmac_secret' => '1234567890',
    ],

    'services' => [
        'accounts' => AccountService::class,
        'policies' => PolicyService::class,
        'mrv' => MrvService::class,
    ]

];

