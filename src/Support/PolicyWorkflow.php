<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;

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

        // TODO: WIP
        //        $block = $this->context->block-fromTag($this->context->policyId, $tag);
        //
        //        ray($block);

        return $this->context->block->dataByTag($this->context->policyId, $tag);
    }

    public function assignRole(GuardianRole $role): bool
    {
        return $this->context->block->assignRole($this->context->policyId, $role);
    }

    public function sendDocumentToTag($tag, array $data): object
    {
        return $this->context->block->sendDocumentToTag($this->context->policyId, $tag, $data);
    }

    public function sendDataToTag($tag, $data): object
    {
        return $this->context->block->sendToTag($this->context->policyId, $tag, $data);
    }
}
