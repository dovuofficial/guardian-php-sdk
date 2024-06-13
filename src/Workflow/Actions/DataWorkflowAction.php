<?php

namespace Dovu\GuardianPhpSdk\Workflow\Actions;

use Dovu\GuardianPhpSdk\Workflow\Abstract\AbstractWorkflowTask;
use Dovu\GuardianPhpSdk\Workflow\Model\GuardianActionTransaction;
use Dovu\GuardianPhpSdk\Workflow\Model\WorkflowActionResult;

class DataWorkflowAction extends AbstractWorkflowTask
{
    public function __construct(GuardianActionTransaction $transaction)
    {
        parent::__construct($transaction);
    }

    public function run(): WorkflowActionResult
    {
        $data = $this->prepareBlockSubmissionPayload();
        $result = $this->send($data);

        return new WorkflowActionResult($result);
    }
}
