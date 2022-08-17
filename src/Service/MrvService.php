<?php

namespace Dovu\GuardianPhpSdk\Service;

class MrvService extends AbstractService
{
    public function submitAgrecalcDocument(string $policyId, string $document)
    {
        if(!is_array($document)){
            $document = json_decode($document, true);
        }

        return $this->client->postJson("policies/{$policyId}/mrv/agrecalc", $document);
    }

    
    public function submitCoolFarmToolDocument(string $policyId, string $document)
    {
        if(!is_array($document)){
            $document = json_decode($document, true);
        }

        return $this->client->postJson("policies/{$policyId}/mrv/cool-farm-tool", $document);
    }


    public function approveMrvDocument($policyId, $did)
    {
        return $this->client->put("policies/{$policyId}/approve/mrv/{$did}");
    }
}
