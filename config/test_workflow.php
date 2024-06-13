<?php

use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\GuardianApprovalOption;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask;

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

    // TODO: Figure out concept/method to connect data between nodes
    'workflow' => [
        [
            "role" => GuardianRole::SUPPLIER,
            "tag" => "create_ecological_project",
            "type" => WorkflowTask::DATA,
        ],
        [
            "role" => GuardianRole::OWNER,
            "filter" => [
                "tag" => "supplier_grid_filter",
                "key" => "uuid",
            ],
            "source_tag" => "supplier_grid",
            "tag" => "approve_supplier_btn",
            "options" => [
                "approve" => [
                    "status" => EntityStatus::APPROVED->value,
                    "option" => GuardianApprovalOption::APPROVE->value,
                ],
                "reject" => [
                    "status" => EntityStatus::REJECTED->value,
                    "option" => GuardianApprovalOption::DENY->value,
                ],
            ],
            "type" => WorkflowTask::APPROVAL,
        ],
        [
            "role" => GuardianRole::SUPPLIER,
            "tag" => "create_site_form",
            "source_tag" => "create_site_form",
            "type" => WorkflowTask::DATA,
            "allow_many" => true, // Ability for the same role to create many instances.
        ],
        [
            "role" => GuardianRole::OWNER,
            "filter" => [
                "tag" => "site_grid_owner_filter",
                "key" => "uuid",
            ],
            "source_tag" => "approve_sites_grid",
            "tag" => "approve_site_button",
            "options" => [
                "approve" => [
                    "status" => EntityStatus::APPROVED->value,
                    "option" => GuardianApprovalOption::APPROVE->value,
                ],
                "reject" => [
                    "status" => EntityStatus::REJECTED->value,
                    "option" => GuardianApprovalOption::DENY->value,
                ],
            ],
            "type" => WorkflowTask::APPROVAL,
        ],
        [
            "role" => GuardianRole::SUPPLIER,
            "tag" => "create_claim_request_form",
            "filter" => [
                "tag" => "site_grid_supplier_filter",
                "key" => "uuid",
            ],
            "source_tag" => "sites_grid",
            "type" => WorkflowTask::DATA,
            "allow_many" => true, // Ability for the same role to create many instances.
        ],
        [
            "role" => GuardianRole::VERIFIER,
            "filter" => [
                "tag" => "claim_request_verifier_filter",
                "key" => "uuid",
            ],
            "source_tag" => "claim_requests_grid(verifier)",
            "tag" => "approve_claim_requests_btn",
            "options" => [
                "approve" => [
                    "status" => EntityStatus::APPROVED->value,
                    "option" => GuardianApprovalOption::APPROVE->value,
                ],
                "reject" => [
                    "status" => EntityStatus::REJECTED->value,
                    "option" => GuardianApprovalOption::DENY->value,
                ],
            ],
            "type" => WorkflowTask::APPROVAL,
        ],
    ],
];

