<?php

namespace Dovu\GuardianPhpSdk\Service;

class SchemaService extends AbstractService
{
    public function get(string $policyId): array
    {
        return (array) $this->httpClient->get("schemas/$policyId?category=POLICY");
    }
}
