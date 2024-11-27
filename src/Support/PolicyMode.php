<?php

namespace Dovu\GuardianPhpSdk\Support;

use Dovu\GuardianPhpSdk\Domain\TaskInstance;

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

    public function publishVersion(callable $callback, string $version): TaskInstance
    {
        return $this->context->policies->publishSync($callback, $this->context->policyId, $version);
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
