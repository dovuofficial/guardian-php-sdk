<?php

namespace Dovu\GuardianPhpSdk\Workflow\Model;

class WorkflowActionResult
{
    public function __construct(public $result)
    {
    }

    public function getDocument(): object
    {
        return (object) $this->result->document;
    }

    public function getId(): string
    {
        return $this->getDocument()->id;
    }
}
