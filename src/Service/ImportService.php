<?php

namespace Dovu\GuardianPhpSdk\Service;

class ImportService extends AbstractService
{
    public const MAX_TRIES = 60;

    private function task(string $id): array
    {
        return (array) $this->httpClient->get("tasks/$id");
    }

    private function runnerTask(string $id, callable $fn, int $run = 0): object
    {
        $data = (object) $this->task($id);

        $fn($data);

        if ($data->result || $run > self::MAX_TRIES) {
            return $data;
        }

        sleep(3);

        return $this->runnerTask($id, $fn, ++$run);
    }

    private function preview(string $timestamp): array
    {
        return (array) $this->httpClient->post(
            "policies/push/import/message/preview",
            [ "messageId" => $timestamp ],
            true
        );
    }

    private function policy(string $timestamp): array
    {
        return (array) $this->httpClient->post(
            "policies/push/import/message",
            [ "messageId" => $timestamp ],
            true
        );
    }

    public function fromTimestamp(callable $callback, string $timestamp)
    {
        $callback($timestamp);

        $import = (object) $this->policy($timestamp);

        $this->runnerTask($import->taskId, $callback);
    }
}
