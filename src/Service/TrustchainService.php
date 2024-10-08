<?php

namespace Dovu\GuardianPhpSdk\Service;

class TrustchainService extends AbstractService
{
    public function all(): array
    {
        return $this->httpClient->get('trustchains')->data();
    }

    public function byHash(string $hash): object
    {
        return (object) $this->httpClient->get("trustchains/{$hash}")->data();
    }
}
