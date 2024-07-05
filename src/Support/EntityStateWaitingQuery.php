<?php

namespace Dovu\GuardianPhpSdk\Support;

// A factory builder for listening to the state change for new entities that have updated
use Dovu\GuardianPhpSdk\Constants\EntityStatus;
use Dovu\GuardianPhpSdk\Constants\StateQuery;

class EntityStateWaitingQuery
{
    public const DEFAULT_FILTER_KEY = 'uuid';

    /**
     * For now, we will assume we are looking for a single key field to filter
     * In this case we class this as our uuid class
     **/
    private string $filter_key = self::DEFAULT_FILTER_KEY;

    /**
     * Value (normally an uuid) to filter query on.
     * @var
     */
    private $filter_value;

    /**
     * Time in seconds where the listener will wait before
     * @var int
     */
    public int $wait_seconds = 2;

    /**
     * Set the status of entity searching for, default to a "waiting"
     * @var EntityStatus
     */
    public EntityStatus $status = EntityStatus::WAITING;


    /**
     * The query to be made for a particular role
     * @var StateQuery
     */
    public StateQuery $query = StateQuery::PROJECTS;

    /**
     * Maximum number of tries to query the
     * @var int
     */
    public int $tries = 40;

    /**
     * Keep track of all attempts for waiting for query state
     * @var int
     */
    private int $attempts = 1;

    public static function instance(): self
    {
        return new self();
    }

    /**
     * Set the main filter for the API, normally searching by a UUID.
     *
     * @param $value
     * @param string $key
     * @return self
     */
    public function filter($value, string $key = self::DEFAULT_FILTER_KEY): self
    {
        $this->filter_value = $value;
        $this->filter_key = $key;

        return $this;
    }

    /**
     * @param EntityStatus $status
     * @return $this
     */
    public function status(EntityStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param StateQuery $query
     * @return $this
     */
    public function query(StateQuery $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param int $wait_seconds
     * @return $this
     */
    public function waitSecs(int $wait_seconds): self
    {
        $this->wait_seconds = $wait_seconds;

        return $this;
    }

    /**
     * @return void
     */
    public function incrementAttempt(): void
    {
        $this->attempts++;
    }

    /**
     * @return bool
     */
    public function canAttemptQuery(): bool
    {
        return $this->attempts <= $this->tries;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return [ $this->filter_key => $this->filter_value ];
    }

    public function attemptMessage(): string
    {
        return "attempt #$this->attempts query for " . $this->query->value . " for status " . $this->status->value;
    }
}
