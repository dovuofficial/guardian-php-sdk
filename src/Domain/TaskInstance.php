<?php

namespace Dovu\GuardianPhpSdk\Domain;

/**
 * TODO: Consider a concept of an "immutable" TaskInstance, where on every object upda
 */
class TaskInstance
{
    public const MAX_TRIES = 60;

    public const TASK_POLL_WAIT = 2; // second/s

    /**
     * These below fields focus on the instance of the task that currently trackable
     */
    public string $id;
    public int $expectation;
    public string $action;
    public string $user_id;
    public bool $complete;

    // Keeps track of the current task instance running cycle
    public int $run = 0;

    // Holds any error that occurs in the task running process
    public object $error;

    // Statuses of the current running task
    public array $statuses;

    public string|array $result;

    /**
     * @param object $response
     */
    public function __construct(object $response)
    {
        $this->id = $response->taskId;
        $this->expectation = $response->expectation;
        $this->action = $response->action;
        $this->user_id = $response->userId;
    }

    public static function from(object $response): self
    {
        return new self($response);
    }

    public function exhaustedTries(): bool
    {
        return $this->run > self::MAX_TRIES;
    }

    public function complete(): self
    {
        $this->complete = true;

        return $this;
    }

    public function pause(): self
    {
        sleep(self::TASK_POLL_WAIT);

        return $this;
    }

    public function incrementAttempt(): self
    {
        ++$this->run;

        return $this;
    }

    public function setError(object $error): void
    {
        $this->error = $error;
    }

    public function updateStatuses(array $statuses): void
    {
        $this->statuses = $statuses;
    }

    public function setResult(array|string $result): self
    {
        $this->result = $result;

        return $this;
    }
}
