<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\StateQuery;

class StateService extends AbstractService
{
    /**
     * Filters, in addition to the status to query entities
     *
     * @var array
     */
    protected array $filters = [];

    /**
     * @deprecated v3 release
     *
     * @param string $policyId
     * @param StateQuery $query
     * @return object
     */
    public function query(string $policyId, StateQuery $query)
    {
        $filters = http_build_query($this->filters);

        return (object) $this->httpClient->get("policies/{$policyId}/state/{$query->value}?{$filters}");
    }

    /**
     * Example usage: $sdk->state->status(EntityStatus::WAITING)->query($this->policyId, $query);
     * @param EntityStatus $status
     * @return $this
     */
    public function status(EntityStatus $status): static
    {
        $this->filters['status'] = $status->value;

        return $this;
    }

    /**
     *  Example usage:
     *      $sdk->state
     *          ->filters([ 'uuid' => 'uuid' ])
     *          ->status(EntityStatus::WAITING)
     *          ->query($this->policyId, $query);
     * @param array $filters
     * @return $this
     */
    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }
}
