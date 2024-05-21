<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\CredentialDocumentBlock;

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

    public function dataByTagToDocumentBlock($tag, EntityStatus $status = null): CredentialDocumentBlock
    {
        return $this->context->block->dataByTagToCredentialBlock($this->context->policyId, $tag, $status);
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

    /**
     * Used for new documents from a particular actor, like a supplier sending the initial
     * project to the registry.
     **/
    public function sendDocumentToTag($tag, array $data): object
    {
        $document = [
            'document' => $data,
        ];

        return $this->context->block->sendToTag($this->context->policyId, $tag, $document);
    }

    // Used for document modification (Approvals and refs down stream)
    public function sendDataToTag($tag, $data): object
    {
        return $this->context->block->sendToTag($this->context->policyId, $tag, $data);
    }

    /**
     * This is an opinionated method to allow the extraction of a trustchain from a "claim" uuid,
     * or the last uuid used for the minting of an ecological credit.
     *
     * We are assuming that there are particular tags in the workflow, without them this could be considered
     * dangerous to use and would require more defensive code.
     *
     * @param string $claim_state_uuid
     * @return CredentialDocumentBlock
     */
    public function trustchainForCreditMint(string $claim_state_uuid)
    {
        $this->filterByTag("vp_filter_grid", $claim_state_uuid);

        $data = $this->dataByTagToDocumentBlock("vp_grid", EntityStatus::MINTING);

        $hash = $data->getHash();

        $this->filterByTag("trustChainBlock", $hash);

        return $this->dataByTagToDocumentBlock("trustChainBlock");
    }
}
