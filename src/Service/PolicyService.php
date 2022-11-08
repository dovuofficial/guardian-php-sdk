<?php

namespace Dovu\GuardianPhpSdk\Service;

class PolicyService extends AbstractService
{
    /**
     *
     * @param string $policyId
     * @param string $document
     * @return void
     */
    public function registerApplication(string $policyId, string $document)
    {
        if (! is_array($document)) {
            $document = json_decode($document, true);
        }

        return $this->httpClient->post(uri: "policies/{$policyId}/register", payload: $document, jsonRequest: true);
    }

    /**
     *
     * @param string $policyId
     * @param string $did
     * @return void
     */
    public function approveApplication(string $policyId, string $did)
    {
        return $this->httpClient->put("policies/{$policyId}/approve/application/{$did}");
    }

    /**
     *
     * @param string $policyId
     * @param string $document
     * @return void
     */
    public function submitProject(string $policyId, string $document)
    {
        if (! is_array($document)) {
            $document = json_decode($document, true);
        }

        return $this->httpClient->post(uri: "policies/{$policyId}/project", payload: $document, jsonRequest: true);
    }

    /**
     *
     * @param string $policyId
     * @param string $did
     * @return void
     */
    public function approveProject(string $policyId, string $did)
    {
        return $this->httpClient->put("policies/{$policyId}/approve/project/{$did}");
    }

    /**
     *
     * @param string $policyId
     * @return void
     */
    public function trustChain(?string $policyId)
    {
        if ($policyId === null) {
            return [];
        }

        return $this->httpClient->get("policies/{$policyId}/trustchains");
    }

    /**
     *
     * @param string $policyId
     * @return void
     */
    public function token(string $policyId)
    {
        return $this->httpClient->get("policies/{$policyId}/token");
    }
}
