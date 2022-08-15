<?php

namespace Dovu\GuardianPhpSdk\Service;

class PolicyService extends AbstractService
{
    
    public function registerApplication(string $policyId, string $document)
    {
        if(!is_array($document)){
            $document = json_decode($document, true);
        }

        return $this->client->postJson("policies/{$policyId}/register", $document);
    }

    public function submitProject(string $policyId, string $document)
    {
        if(!is_array($document)){
            $document = json_decode($document, true);
        }

        return $this->client->postJson("policies/{$policyId}/project/", $document);
    }


    public function approveApplication(string $policyId, string $did)
    {
        return $this->client->put("policies/{$policyId}/approve/application/{$did}");
    }
}
