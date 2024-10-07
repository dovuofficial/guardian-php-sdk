<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Domain\GuardianToken;
use Exception;

class PolicyService extends AbstractService
{
    public function all(): array
    {
        return $this->httpClient->get('policies')->data();
    }

    public function get($id): object
    {
        return (object) $this->httpClient->get("policies/{$id}")->data();
    }

    public function assign(string $username, string $policy_id, bool $assign = true): object
    {
        $payload = [
            "assign" => $assign,
            "policyIds" => [
                $policy_id,
            ],
        ];

        return (object) $this->httpClient->post(
            "permissions/users/{$username}/policies/assign",
            $payload,
            true
        )->data();
    }

    /**
     *
     * Going to make an assumption for this version of the SDK that there is only one token that is
     * published, might need to revisit.
     *
     * @param string $policyId
     * @return GuardianToken
     */
    public function token(string $policyId): GuardianToken
    {

        $tokens = $this->httpClient->get("tokens?policyId={$policyId}&status=All")->data();

        // object-ify Hack.
        $as_list = json_decode(json_encode($tokens));

        if (empty($as_list)) {
            return GuardianToken::none();
        }

        // Get first entry for tokens (might be improved later).
        $token = $as_list[0];

        return new GuardianToken($token);
    }
}
