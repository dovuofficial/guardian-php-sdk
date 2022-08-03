<?php

namespace Dovu\GuardianPhpSdk\Service;

class PolicyService extends AbstractService
{
    
    public function register($policyId, $document)
    {
        if(!is_array($document)){
            $document = json_decode($document);
        }

        return $this->client->post("policies/{$policyId}/register", [
            'json' => $document
        ]);
    }
}
