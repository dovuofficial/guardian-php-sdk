<?php

namespace Dovu\GuardianPhpSdk\Workflow\Abstract;

use Dovu\GuardianPhpSdk\Domain\CredentialDocumentBlock;
use Dovu\GuardianPhpSdk\Support\EntityStateWaitingQuery;
use Dovu\GuardianPhpSdk\Workflow\Contracts\GuardianWorkflowAction;
use Dovu\GuardianPhpSdk\Workflow\Model\GuardianActionTransaction;
use Dovu\GuardianPhpSdk\Workflow\Model\WorkflowActionResult;

abstract class AbstractWorkflowTask implements GuardianWorkflowAction
{
    private EntityStateWaitingQuery $requiredStateQuery;

    protected function __construct(
        public GuardianActionTransaction $transaction
    ) {
        $this->requiredStateQuery = EntityStateWaitingQuery::instance();
    }

    /**
     * @throws \Exception
     */
    public function ensureStatusRequirement(CredentialDocumentBlock $block): bool
    {
        $state = $this->requiredStateQuery;

        if (! $state->canAttemptQuery()) {
            throw new \Exception('Entity query attempts exhausted.');
        }

        $status_required = $this->transaction->element->statusRequirement();

        if (! $status_required) {
            return true;
        }

        $has_status = $block->hasExpectedStatus($status_required);

        if ($has_status) {
            return true;
        }

        ray("reattempt in $state->wait_seconds seconds");

        sleep($state->wait_seconds);

        $state->incrementAttempt();

        return false;
    }

    public function send(array $data): object
    {
        $tx = $this->transaction;
        $workflow = $tx->mediator->workflow;
        $tag = $tx->element->tag;

        if ($tx->element->sourceTag()) {
            return $workflow->sendDataToTag($tag, $data);
        }

        return $workflow->sendDocumentToTag($tag, $data);
    }

    public function prepareBlockSubmissionPayload(): array
    {
        $tx = $this->transaction;
        $data = $tx->payload;
        $source_tag = $tx->element->sourceTag();

        if ($source_tag) {
            $document = $this->fetchBlockDataUsingOptionalFilter();

            return $document->chainDocumentAsReference($data);
        }

        return $data;
    }

    public function fetchBlockDataUsingOptionalFilter(): CredentialDocumentBlock
    {
        $tx = $this->transaction;
        $workflow = $tx->mediator->workflow;

        if ($tx->element->hasFilter()) {
            $filter_value = $tx->filter_value;
            $element_filter = $tx->element->getFilter();

            $workflow->filterByTag($element_filter->tag, $filter_value);
        }

        ray('before $block');

        $block = $workflow->dataByTagToDocumentBlock($tx->element->sourceTag());

        if (! $block) {
            $state = $this->requiredStateQuery;
            ray("'NO DATA FOUND' for block reattempt in $state->wait_seconds seconds");

            sleep($state->wait_seconds);

            return $this->fetchBlockDataUsingOptionalFilter();
        }

        if (! $this->ensureStatusRequirement($block)) {
            return $this->fetchBlockDataUsingOptionalFilter();
        }

        return $block;
    }

    public function updateApprovalBlockState(CredentialDocumentBlock $data): CredentialDocumentBlock
    {
        $tx = $this->transaction;
        $option = $tx->element->getDefinedOption($tx->approvalOption);

        $data->updateStatus($option->status);
        $data->assignTag($option->option);

        return $data;
    }

    public function validate(): bool
    {
        // TODO: Implement validate() method (requires custom class)
        return true;
    }

    abstract public function run(): WorkflowActionResult;
}
