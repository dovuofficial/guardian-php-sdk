<?php

use Dovu\GuardianPhpSdk\Service\AccountService;
use Dovu\GuardianPhpSdk\Service\PolicyService;

return [

    'services' => [
        'accounts' => AccountService::class,
        'policies' => PolicyService::class,
    ]

];