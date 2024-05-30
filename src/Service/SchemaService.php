<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Domain\CredentialDocumentBlock;

class SchemaService extends AbstractService
{
    public function get(string $policyId): array
    {
        return (array) $this->httpClient->get("schemas/$policyId?category=POLICY");
    }

}
