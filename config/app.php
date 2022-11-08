<?php

use Dovu\GuardianPhpSdk\Service\AccountService;
use Dovu\GuardianPhpSdk\Service\MrvService;
use Dovu\GuardianPhpSdk\Service\PolicyService;

return [

    'app' => [
        'base_url' => 'http://localhost:3001/api/',

    ],

    'local' => [
        'policy_id' => '632b33532e11b6094416fc14',
        'hmac_secret' => '1234567890',
    ],

    'services' => [
        'accounts' => AccountService::class,
        'policies' => PolicyService::class,
        'mrv' => MrvService::class,
    ],

];

