<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\CredentialDocumentBlock;

class BlockService extends AbstractService
{
    public function dataByTag(string $policyId, string $tag, string $uuid = null): object
    {
        $route = "policies/{$policyId}/tag/{$tag}/blocks";

        if ($uuid) {
            $route .= "?filterByUUID={$uuid}";
        }

        ray($route);

        return (object) $this->httpClient->get($route)->data();
    }

    public function dataByTagWithUuid(string $policyId, string $tag, ?string $uuid = null): ?CredentialDocumentBlock
    {
        $data = $this->dataByTag($policyId, $tag, $uuid);

        // poop.
        $data_predicate = $uuid ? $data->data[0] : $data->data['document'];

        if (! $data_predicate) {
            return null;
        }

        return new CredentialDocumentBlock($data);
    }

    public function dataByTagToCredentialBlock(string $policyId, string $tag, EntityStatus $status = null): ?CredentialDocumentBlock
    {
        $data = $this->dataByTag($policyId, $tag);

        if (! $data->data) {
            return null;
        }

        return new CredentialDocumentBlock($data, $status);

    }

    // Might not work due to tag instead of blocks
    public function filterByTag(string $policyId, string $tag, string $uuid): object
    {
        // TODO: This anomaly should be removed later as the Guardian needs to manually reset filterable state related to a new filter.
        $this->dataByTag($policyId, $tag);

        return (object) $this->httpClient->post("policies/{$policyId}/tag/{$tag}/blocks", [
            'filterValue' => $uuid,
        ], true)->data();
    }

    public function dataById(string $policyId, string $id): array
    {
        return $this->httpClient->get("policies/{$policyId}/blocks/")->data();
    }

    public function fromTag(string $policyId, string $tag): object
    {
        return (object) $this->httpClient->get("policies/{$policyId}/tag/{$tag}")->data();
    }

    public function sendToTag(string $policyId, string $tag, $data): object
    {
        return (object) $this->httpClient->post("policies/{$policyId}/tag/{$tag}/blocks", (array) $data, true)->data();
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
        return $this->sendToTag($policyId, 'choose_role', $data);
    }
}
