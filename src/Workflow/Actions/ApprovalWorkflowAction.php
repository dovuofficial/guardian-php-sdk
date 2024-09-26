<?php

namespace Dovu\GuardianPhpSdk\Workflow\Actions;

use Dovu\GuardianPhpSdk\Workflow\Abstract\AbstractWorkflowTask;
use Dovu\GuardianPhpSdk\Workflow\Model\GuardianActionTransaction;
use Dovu\GuardianPhpSdk\Workflow\Model\WorkflowActionResult;

class ApprovalWorkflowAction extends AbstractWorkflowTask
{
    public function __construct(GuardianActionTransaction $context)
    {
        parent::__construct($context);
    }

    public function run(): WorkflowActionResult
    {
        $filtered_block = $this->fetchBlockDataUsingOptionalFilter();
        $approval_block = $this->updateApprovalBlockState($filtered_block);

        $result = $this->send($approval_block->forDocumentSubmission());

        return new WorkflowActionResult($result);
    }
}
