<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\CredentialDocumentBlock;
use Dovu\GuardianPhpSdk\Domain\FilterVerifiableCredentialBlock;

/**
 * The policy workflow only cares about retrieving and submitting data from/to blocks.
 *
 * It assumes that the role is independent and is already managed, it might do error handling roles that may not match, but that is a problem for later.
 */
class PolicyWorkflow
{
    private PolicyContext $context;

    private function __construct(PolicyContext $context)
    {
        $this->context = $context;
    }

    public static function context(PolicyContext $context): self
    {
        return new self($context);
    }

    public function dataByTag($tag): object
    {
        return $this->context->block->dataByTag($this->context->policyId, $tag);
    }

    public function dataByTagToFilterBlock($tag): FilterVerifiableCredentialBlock
    {
        return $this->context->block->dataByTagToFilterBlock($this->context->policyId, $tag);
    }

    public function dataByTagToDocumentBlock($tag): CredentialDocumentBlock
    {
        return $this->context->block->dataByTagToCredentialBlock($this->context->policyId, $tag);
    }

    // TODO: Expect current filter to be a externally generated uuid. (policy defined)
    public function filterByTag($tag, $uuid): object
    {
        return $this->context->block->filterByTag($this->context->policyId, $tag, $uuid);
    }

    public function assignRole(GuardianRole $role): bool
    {
        return $this->context->block->assignRole($this->context->policyId, $role);
    }

    // Used for new documents from a particular actor
    public function sendDocumentToTag($tag, array $data): object
    {
        return $this->context->block->sendDocumentToTag($this->context->policyId, $tag, $data);
    }

    // Used for document modification (Approvals and refs down stream)
    public function sendDataToTag($tag, $data): object
    {
        return $this->context->block->sendToTag($this->context->policyId, $tag, $data);
    }
}
