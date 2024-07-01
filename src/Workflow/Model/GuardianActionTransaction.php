<?php

namespace Dovu\GuardianPhpSdk\Workflow\Model;

use Dovu\GuardianPhpSdk\Workflow\Constants\ApprovalOption;
use Dovu\GuardianPhpSdk\Workflow\GuardianActionMediator;

class GuardianActionTransaction
{
    public array $payload = [];

    public WorkflowElement $element;

    public GuardianActionMediator $mediator;

    public ?ApprovalOption $approvalOption = null;

    public ?string $filter_value = null;

    /**
     * @param GuardianActionMediator $mediator
     */
    private function __construct(GuardianActionMediator $mediator)
    {
        $this->mediator = $mediator;
    }

    public static function with(GuardianActionMediator $mediator): self
    {
        return new self($mediator);
    }

    /**
     * @param GuardianActionMediator $mediator
     * @return GuardianActionTransaction
     */
    public function setMediator(GuardianActionMediator $mediator): self
    {
        $this->mediator = $mediator;

        return $this;
    }

    /**
     * @param WorkflowElement $element
     * @return GuardianActionTransaction
     */
    public function setWorkflowElement(WorkflowElement $element): self
    {
        $this->element = $element;

        return $this;
    }

    /**
     * @param array $payload
     * @return GuardianActionTransaction
     */
    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function setApprovalOption(ApprovalOption $approvalOption): self
    {
        $this->approvalOption = $approvalOption;

        return $this;
    }

    public function setFilterValue(string $filter_value): self
    {
        $this->filter_value = $filter_value;

        return $this;
    }

    public function run(): WorkflowActionResult
    {
        return $this->mediator->run($this);
    }
}
