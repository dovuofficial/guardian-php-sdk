<?php

namespace Dovu\GuardianPhpSdk\Contracts;

use Dovu\GuardianPhpSdk\Domain\HttpClientResponse;

interface HttpClientInterface
{
    public function get(string $uri): HttpClientResponse;

    public function post(string $uri, array $payload = [], bool $jsonRequest = false): HttpClientResponse;

    public function put(string $uri, array $payload = []): HttpClientResponse;
}
