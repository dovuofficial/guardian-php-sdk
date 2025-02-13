<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\Constants\BlockKey;
use Dovu\GuardianPhpSdk\Constants\BlockType;
use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\CredentialDocumentBlock;
use Dovu\GuardianPhpSdk\Domain\PolicyConfiguration;
use Dovu\GuardianPhpSdk\Domain\TrustchainQuery;

/**
 * The policy workflow only cares about retrieving and submitting data from/to blocks.
 *
 * It assumes that the role is independent and is already managed, it might do error handling roles that may not match, but that is a problem for later.
 */
class PolicyWorkflow
{
    public PolicyContext $context;

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

    public function dataByTagToDocumentBlock($tag, EntityStatus $status = null): ?CredentialDocumentBlock
    {
        return $this->context->block->dataByTagToCredentialBlock($this->context->policyId, $tag, $status);
    }

    // TODO: Expect current filter to be a externally generated uuid. (policy defined)
    public function filterByTag($tag, $uuid): object
    {
        return $this->context->block->filterByTag($this->context->policyId, $tag, $uuid);
    }

    public function assignRole(GuardianRole $role): object
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

    public function scanForBlockType(array $children, BlockType $target = BlockType::MINT_BLOCK, array &$res = []): array
    {
        foreach ($children as $child) {
            $c = (object) $child;

            if ($c->blockType === $target->value) {
                $res[] = $c;
            }

            $this->scanForBlockType($c->children, $target, $res);
        }

        return $res;
    }

    public function mintBlockReference(): object
    {
        $conf = $this->getConfiguration();
        $root_children = $conf->policy->config["children"];

        $mint_blocks = $this->scanForBlockType($root_children);

        if (empty($mint_blocks)) {
            throw new \Exception('Mint blocks not found');
        }

        // Assume single or first mint block in policy
        return $mint_blocks[0];
    }

    /**
     * This is an opinionated method to allow the extraction of a trustchain from a "claim" uuid,
     * or the last uuid used for the minting of an ecological credit.
     *
     * We are assuming that there are particular tags in the workflow, without them this could be considered
     * dangerous to use and would require more defensive code.
     *
     * @param TrustchainQuery $query
     * @return TrustchainQuery
     */
    public function trustchainRequest(TrustchainQuery $query): TrustchainQuery
    {
        /**
         * TODO: extract tags
         */
        $this->filterByTag("vp_filter_grid", $query->uuid);

        $data = $this->dataByTagToDocumentBlock("vp_grid", EntityStatus::MINTING);

        if ($data && $data->hasBlockData()) {
            $hash = $data->getHash();

            $this->filterByTag("trustChainBlock", $hash);

            $result = $this->dataByTagToDocumentBlock("trustChainBlock");

            return $query->withResult($result);
        }

        if ($query->canAttemptQuery()) {
            $query->defer();

            return $this->trustchainRequest($query);
        }

        return $query;
    }

    public function getPolicy(): object
    {
        return $this->context->policies->get($this->context->policyId);
    }

    public function getPolicySchemas(): array
    {
        $policy = $this->getPolicy();

        return $this->context->schema->get($policy->topicId);
    }

    public function getRoles(): array
    {
        return $this->getPolicy()->policyRoles;
    }

    public function getConfiguration(): PolicyConfiguration
    {
        return new PolicyConfiguration(workflow: $this);
    }

    public function getSchemaForKey(string $v, BlockKey $ky = BlockKey::IRI): object
    {
        $schemas = $this->getPolicySchemas();

        $filter = array_filter($schemas, fn ($elem) => $elem[$ky->value] == $v);

        return (object) current($filter);
    }
}
