<?php

namespace Dovu\GuardianPhpSdk\Experimental;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask;

class PaladinBlockElement
{
    public ?GuardianRole $role;

    public ?string $tag;

    public ?WorkflowTask $type;

    public ?string $key = null;

    public function __construct()
    {
    }

    public static function new()
    {
        return new self();
    }

    public function toObject(): object
    {
        return (object) [
            "role" => $this->role,
            "tag" => $this->tag,
            "type" => $this->type,
            "key" => $this->key,
        ];
    }

    public function setRole(?GuardianRole $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function setWorkflowTask(?WorkflowTask $workflowTask): self
    {
        $this->type = $workflowTask;

        return $this;
    }

    public function setKey(?string $key): self
    {
        $this->key = $key;

        return $this;
    }
}
