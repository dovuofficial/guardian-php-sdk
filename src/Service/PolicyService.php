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

        return $this->client->postJson("policies/{$policyId}/register", $document);
    }

    /**
     *
     * @param string $policyId
     * @param string $did
     * @return void
     */
    public function approveApplication(string $policyId, string $did)
    {
        return $this->client->put("policies/{$policyId}/approve/application/{$did}");
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

        return $this->client->postJson("policies/{$policyId}/project", $document);
    }

    /**
     *
     * @param string $policyId
     * @param string $did
     * @return void
     */
    public function approveProject(string $policyId, string $did)
    {
        return $this->client->put("policies/{$policyId}/approve/project/{$did}");
    }

    /**
     *
     * @param string $policyId
     * @return void
     */
    public function trustChain(string $policyId)
    {
        return $this->client->get("policies/{$policyId}/trustchains");
    }

}
