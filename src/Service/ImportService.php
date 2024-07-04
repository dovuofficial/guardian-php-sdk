<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Domain\TaskInstance;

class ImportService extends AbstractService
{
    private function preview(string $timestamp): array
    {
        return (array) $this->httpClient->post(
            "policies/push/import/message/preview",
            [ "messageId" => $timestamp ],
            true
        )->data();
    }

    private function policy(string $timestamp): object
    {
        return (object) $this->httpClient->post(
            "policies/push/import/message",
            [ "messageId" => $timestamp ],
            true
        )->data();
    }

    public function fromTimestamp(callable $callback, string $timestamp): TaskInstance
    {
        $callback($timestamp);

        $import = TaskInstance::from($this->policy($timestamp));

        return $this->runnerTask($import, $callback);
    }
}
