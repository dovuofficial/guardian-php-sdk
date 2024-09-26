<?php

namespace Dovu\GuardianPhpSdk\Workflow\Model;

use Dovu\GuardianPhpSdk\Constants\GuardianRole;
use Dovu\GuardianPhpSdk\Workflow\Constants\ApprovalOption;
use Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask;

class WorkflowElement
{
    public string $tag;

    public GuardianRole $role;

    public WorkflowTask $type;

    private function __construct(
        public object $item
    ) {
        $this->tag = $item->tag;
        $this->role = $item->role;
        $this->type = $item->type;
    }

    public static function parse(object $item): WorkflowElement
    {
        return new self($item);
    }

    public function allowMany(): bool
    {
        return $this->item->allow_many;
    }

    public function hasSourceTag(): bool
    {
        return $this->item->source_tag;
    }

    public function hasFilter(): bool
    {
        return ! ! ($this->item->filter ?? null);
    }

    public function hasRequirement(): bool
    {
        return ! ! ($this->item->require ?? null);
    }

    public function statusRequirement(): ?string
    {
        return ($this->item?->require?->status ?? null);
    }

    public function getFilter(): object
    {
        return (object) $this->item->filter;
    }

    public function getDefinedOption(ApprovalOption $option): object
    {
        return (object) $this->item->options[$option->value];
    }

    public function sourceTag(): ?string
    {
        return $this->item->source_tag ?? null;
    }
}
