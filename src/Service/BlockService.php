<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;

class BlockService extends AbstractService
{
    public function dataByTag(string $policyId, string $tag): object
    {
        return (object) $this->httpClient->get("policies/{$policyId}/tag/{$tag}/blocks");
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

    public function sendDocumentToTag(string $policyId, $tag, array $data): object
    {
        $document = [
            'document' => $data,
        ];

        return $this->sendToTag($policyId, $tag, $document);
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
