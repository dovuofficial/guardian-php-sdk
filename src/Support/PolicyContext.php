<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\DovuGuardianAPI;

class PolicyContext
{
    public DovuGuardianAPI $sdk;

    public string $policyId;

    private function __construct(DovuGuardianAPI $sdk)
    {
        $this->sdk = $sdk;
        $this->policyId = $sdk->config['local']['policy_id'];
    }

    public static function using(DovuGuardianAPI $sdk): self
    {
        return new self($sdk);
    }
    public function for(string $policy_id): self
    {
        $this->policyId = $policy_id;

        return $this;
    }

    public function __get($name)
    {
        try {
            return $this->sdk->$name;
        } catch (\Exception $e) {
            throw new \Exception("Failed to access '{$name}' in SDK. Reason: " . $e->getMessage());
        }
    }

    public function setPolicyId(string $policyId): void
    {
        $this->policyId = $policyId;
    }

    public function setSdk(DovuGuardianAPI $sdk): void
    {
        $this->sdk = $sdk;
    }
}
