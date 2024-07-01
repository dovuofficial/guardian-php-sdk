<?php

use Dovu\GuardianPhpSdk\Constants\GuardianRole;

return [

    /**
     * Import of workflow using IPFS timestamp and testnet
     */
    'import' => [
        'timestamp' => '1717422172.111136584',
        'is_testnet' => true,
    ],

    // TODO: determine if this is required, as roles are referenced in workflow (should it be used for verification).
    'roles' => [
        'supplier' => GuardianRole::SUPPLIER,
        'verifier' => GuardianRole::VERIFIER,
        'owner' => GuardianRole::OWNER,
    ],

    // Use predefined "DOVU" standard template for preparing the workflow.
    'template' => 'dovu',
];

