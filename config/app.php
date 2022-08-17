<?php

use Dovu\GuardianPhpSdk\Service\AccountService;
use Dovu\GuardianPhpSdk\Service\MrvService;
use Dovu\GuardianPhpSdk\Service\PolicyService;

return [

    'app' => [
        'base_url' => 'http://localhost:3001/api/'
    ],

    'services' => [
        'accounts' => AccountService::class,
        'policies' => PolicyService::class,
        'mrv' => MrvService::class,
    ]

];