<?php

namespace Dovu\GuardianPhpSdk\Contracts;

use Exception;

interface HttpClientInterface
{
    public function get(string $uri): bool|array|Exception;

    public function post(string $uri, array $payload = [], bool $jsonRequest = false): bool|array|Exception;

    public function put(string $uri, array $payload = []): bool|array|Exception;
}
