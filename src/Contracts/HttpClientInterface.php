<?php

namespace Dovu\GuardianPhpSdk\Contracts;

use Exception;

interface HttpClientInterface
{
    public function get(string $uri): array|Exception;

    public function post(string $uri, array $payload = [], bool $jsonRequest = false): array|Exception;

    public function put(string $uri, array $payload = []): array|Exception;
}
