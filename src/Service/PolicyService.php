<?php

namespace Dovu\GuardianPhpSdk\Service;

class PolicyService extends AbstractService
{
    /**
     *
     * @param string $policyId
     * @param string $document
     * @return array|\Exception
     */
    public function createProject(string $policyId, string $document)
    {
        if (! is_array($document)) {
            $document = json_decode($document, true);
        }

        return $this->httpClient->post(uri: "policies/{$policyId}/projects", payload: $document, jsonRequest: true);
    }

    /**
     *
     * @param string $policyId
     * @param string $entityId
     * @return array|\Exception
     */
    public function approveProject(string $policyId, string $entityId)
    {
        return $this->httpClient->put("policies/{$policyId}/approval/projects/{$entityId}");
    }

    /**
     *
     * @param string $policyId
     * @param string $projectId
     * @param string|array $document
     * @return array|\Exception
     */
    public function createSite(string $policyId, string $projectId, string|array $document)
    {
        if (! is_array($document)) {
            $document = json_decode($document, true);
        }

        return $this->httpClient->post(uri: "policies/{$policyId}/projects/{$projectId}/sites", payload: $document, jsonRequest: true);
    }

    /**
     *
     * @param string $policyId
     * @param string $entityId
     * @return void
     */
    public function approveSite(string $policyId, string $entityId)
    {
        return $this->httpClient->put("policies/{$policyId}/approval/sites/{$entityId}");
    }


    /**
     *
     * @param string $policyId
     * @param string $siteId
     * @param string|array $document
     * @return array|\Exception
     */
    public function createClaim(string $policyId, string $siteId, string|array $document)
    {
        if (! is_array($document)) {
            $document = json_decode($document, true);
        }

        return $this->httpClient->post(uri: "policies/{$policyId}/sites/{$siteId}/claims", payload: $document, jsonRequest: true);
    }

    /**
     *
     * @param string $policyId
     * @param string $entityId
     * @return array|\Exception
     */
    public function approveClaim(string $policyId, string $entityId)
    {
        return $this->httpClient->put("policies/{$policyId}/approval/claims/{$entityId}");
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
