<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Contracts\HttpClientInterface;
use Dovu\GuardianPhpSdk\Domain\TaskInstance;

class AbstractService
{
    public function __construct(protected HttpClientInterface $httpClient)
    {
    }

    protected function task(string $id): array
    {
        return (array) $this->httpClient->get("tasks/$id")->data();
    }

    /**
     * @throws \Exception
     */
    public function runnerTask(TaskInstance $instance, callable $fn = null): TaskInstance
    {
        // Ensure that every state change of a running task is linked to an immutable instance
        $instance = clone $instance;

        $data = (object) $this->task($instance->id);

        if ($fn) {
            $fn($data);
        }

        /**
         * TODO: Consider this returns the task, and outside can check if complete/errors
         */
        if (isset($data->error)) {
            $error = (object) $data->error;
            $instance->setError($error);

            throw new \Exception("Error for task: [Status - $error->code], with message [$error->message]");
        }

        if ($data->statuses) {
            $instance->updateStatuses($data->statuses);
        }

        if (isset($data->result)) {
            return $instance->setResult($data->result)->complete();
        }

        if ($instance->exhaustedTries()) {
            throw new \Exception("Error for task: [Status - 'Exhausted tries'], with message ['Amount of tries for task $instance->id of action $instance->action were exhausted']");
        }

        return $this->runnerTask($instance->pause()->incrementAttempt(), $fn);
    }

    public function syncRunnerTask(TaskInstance $task): object
    {
        return $this->runnerTask($task);
    }
}
