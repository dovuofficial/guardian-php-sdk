<?php

namespace Dovu\GuardianPhpSdk\Service;

class MrvService extends AbstractService
{

    public function submitAgrecalcDocument(string $policyId, string $document)
    {
        if (! is_array($document)) {
            $document = json_decode($document, true);
        }

        return $this->httpClient->post(uri: "policies/{$policyId}/mrv/agrecalc", payload: $document, jsonRequest: true);
    }



    public function submitCoolFarmToolDocument(string $policyId, string $document)
    {
        if (! is_array($document)) {
            $document = json_decode($document, true);
        }

        return $this->httpClient->post(uri: "policies/{$policyId}/mrv/cool-farm-tool", payload: $document, jsonRequest: true);
    }


    
    public function approveMrvDocument(string $policyId, string $did)
    {
        return $this->httpClient->put("policies/{$policyId}/approve/mrv/{$did}");
    }
}
