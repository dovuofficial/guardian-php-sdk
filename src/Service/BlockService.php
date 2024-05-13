<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\CredentialDocumentBlock;

class BlockService extends AbstractService
{
    public function dataByTag(string $policyId, string $tag): object
    {
        return (object) $this->httpClient->get("policies/{$policyId}/tag/{$tag}/blocks");
    }

    public function dataByTagToCredentialBlock(string $policyId, string $tag): CredentialDocumentBlock
    {
        $data = $this->dataByTag($policyId, $tag);

        return new CredentialDocumentBlock($data);
    }

    // Might not work due to tag instead of blocks
    public function filterByTag(string $policyId, string $tag, string $uuid): object
    {
        // TODO: This anomaly should be removed later as the Guardian needs to manually reset filterable state related to a new filter.
        $this->dataByTag($policyId, $tag);

        return (object) $this->httpClient->post("policies/{$policyId}/tag/{$tag}/blocks", [
            'filterValue' => $uuid,
        ], true);
    }

    public function dataById(string $policyId, string $id): object
    {
        return (object) $this->httpClient->get("policies/{$policyId}/blocks/{$id}");
    }

    public function fromTag(string $policyId, string $tag): object
    {
        return (object) $this->httpClient->get("policies/{$policyId}/tag/{$tag}");
    }

    public function sendToTag(string $policyId, string $tag, $data): object
    {
        return (object) $this->httpClient->post("policies/{$policyId}/tag/{$tag}/blocks", (array) $data, true);
    }

    public function assignRole(string $policyId, GuardianRole $role)
    {
        $data = [
            'role' => $role->value,
        ];

        /**
         * The boolean value from the server is returned as an object, mapping to scalar. We undo this.
         *
         * TODO: Consider building a HTTP response DTO.
         */
        return $this->sendToTag($policyId, 'choose_role', $data)->scalar;
    }
}
