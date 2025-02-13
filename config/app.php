<?php

use Dovu\GuardianPhpSdk\Service\AccountService;
use Dovu\GuardianPhpSdk\Service\BlockService;
use Dovu\GuardianPhpSdk\Service\DryRunService;
use Dovu\GuardianPhpSdk\Service\MrvService;
use Dovu\GuardianPhpSdk\Service\PolicyService;
use Dovu\GuardianPhpSdk\Service\SchemaService;
use Dovu\GuardianPhpSdk\Service\StateService;
use Dovu\GuardianPhpSdk\Service\TagService;
use Dovu\GuardianPhpSdk\Service\TrustchainService;
use Dovu\GuardianPhpSdk\Service\ImportService;

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
        'tag' => TagService::class,
        'dry_run' => DryRunService::class,
        'block' => BlockService::class,
        'schema' => SchemaService::class,
        'import' => ImportService::class,
        'trustchain' => TrustchainService::class,
    ],
];

