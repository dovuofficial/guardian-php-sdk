<?php

namespace Dovu\GuardianPhpSdk\Service;

class TagService extends AbstractService
{
    public function create($tag_payload): object
    {
        return (object) $this->httpClient->post("tags", $tag_payload);
    }

    /**
     * Example usage of policy tagging using DOVU OS, this can be used for other examples
     *
     * @param $policy_id
     * @return object
     */
    public function appendDovuPolicyTag($policy_id): object
    {
        $payload = [
            'target' => $policy_id,
            'entity' => "Policy",
            'name' => "DOVU",
            'description' => "DOVU OS Created Policy"
        ];

        return $this->create($payload);
    }
}
