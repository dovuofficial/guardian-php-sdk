<?php

namespace Dovu\GuardianPhpSdk\Support;

enum PolicyStatus: string
{
    case DRAFT = 'DRAFT';
    case DRY_RUN = 'DRY-RUN';
    case PUBLISHED = 'PUBLISHED';
}

class PolicyMode
{
    private PolicyContext $context;

    private function __construct(PolicyContext $context)
    {
        $this->context = $context;
    }

    public static function context(PolicyContext $context): self
    {
        return new self($context);
    }

    // TODO: Implement later to publish
    public function publish()
    {

    }

    public function dryRun()
    {
        if (! $this->hasPolicyStatus(PolicyStatus::DRY_RUN)) {
            $this->context->dry_run->start($this->context->policyId);
        }
    }

    public function draft()
    {
        if (! $this->hasPolicyStatus(PolicyStatus::DRAFT)) {
            $this->context->dry_run->stop($this->context->policyId);
        }
    }

    public function policyStatus(): string
    {
        return $this->context->policies->get($this->context->policyId)->status;
    }

    public function hasPolicyStatus(PolicyStatus $status): bool
    {
        return $this->policyStatus() === $status->value;
    }
}
