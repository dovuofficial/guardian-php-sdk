<?php

namespace Dovu\GuardianPhpSdk\Workflow\Contracts;

use Dovu\GuardianPhpSdk\Workflow\Model\WorkflowActionResult;

interface GuardianWorkflowAction
{
    // TODO: Validate if workflow unit can be processed (important when creating other workflows)
    public function validate(): bool;

    public function run(): WorkflowActionResult;
}
