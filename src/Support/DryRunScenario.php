<?php

namespace Dovu\GuardianPhpSdk\Support;

class DryRunScenario
{
    public PolicyContext $context;

    private function __construct(PolicyContext $context)
    {
        $this->context = $context;
    }

    public static function context(PolicyContext $context): self
    {
        return new self($context);
    }

    public function restart()
    {
        return $this->context->dry_run->restart($this->context->policyId);
    }

    public function login(string $did): object
    {
        return $this->context->dry_run->login($this->context->policyId, $did);
    }

    public function createUser(): array
    {
        return $this->context->dry_run->createUser($this->context->policyId);
    }

    public function users(): array
    {
        return $this->context->dry_run->users($this->context->policyId);
    }

    public function policyState(): object
    {
        return $this->context->policies->get($this->context->policyId);
    }
}
