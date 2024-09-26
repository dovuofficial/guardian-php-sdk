<?php

namespace Dovu\GuardianPhpSdk\Workflow;

use Dovu\GuardianPhpSdk\Support\PolicyWorkflow;
use Dovu\GuardianPhpSdk\Workflow\Actions\ApprovalWorkflowAction;
use Dovu\GuardianPhpSdk\Workflow\Actions\DataWorkflowAction;
use Dovu\GuardianPhpSdk\Workflow\Constants\WorkflowTask;
use Dovu\GuardianPhpSdk\Workflow\Model\GuardianActionTransaction;
use Dovu\GuardianPhpSdk\Workflow\Model\WorkflowActionResult;

class GuardianActionMediator
{
    private function __construct(
        public PolicyWorkflow $workflow
    ) {
    }

    // This will include the Policy workflow
    public static function with(PolicyWorkflow $workflow): GuardianActionMediator
    {
        return new self($workflow);
    }

    /**
     * @throws \Exception
     */
    public function run(GuardianActionTransaction $transaction): WorkflowActionResult
    {
        $action = match ($transaction->element->type) {
            WorkflowTask::DATA => new DataWorkflowAction($transaction),
            WorkflowTask::APPROVAL => new ApprovalWorkflowAction($transaction),
        };

        if ($action->validate()) {
            return $action->run();
        }

        throw new \Exception("TODO: Failed to validate workflow action");
    }
}
