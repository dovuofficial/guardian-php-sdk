<?php

namespace Dovu\GuardianPhpSdk;

use Exception;
use GuzzleHttp\Client;
use Dovu\GuardianPhpSdk\Exceptions\FailedActionException;
use Dovu\GuardianPhpSdk\Exceptions\NotFoundException;
use Dovu\GuardianPhpSdk\Exceptions\UnauthorizedException;
use Dovu\GuardianPhpSdk\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;

class BaseAPIClient
{
    /** @var string */
    public string $apiToken = "";

    public Client $client;

    private const API_URL = "http://localhost:3001/api/";

    public function __construct()
    {
        $this->client = new Client();


        $this->client = new Client([
            'base_uri' => self::API_URL,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function setApiToken(string $apiToken)
    {
        $this->apiToken = $this->apiToken;
    }

    protected function get(string $uri)
    {
        return $this->request('GET', $uri);
    }

    public function post(string $uri, array $payload = [])
    {
        return $this->request('POST', $uri, $payload);
    }

    protected function put(string $uri, array $payload = [])
    {
        return $this->request('PUT', $uri, $payload);
    }

    protected function delete(string $uri, array $payload = [])
    {
        return $this->request('DELETE', $uri, $payload);
    }

    protected function request(string $verb, string $uri, array $payload = [])
    {
        $response = $this->client->request(
            $verb,
            $uri,
            empty($payload) ? [] : ['form_params' => $payload]
        );

        if (! $this->isSuccessful($response)) {
            return $this->handleRequestError($response);
        }

        $responseBody = (string) $response->getBody();

        return json_decode($responseBody, true) ?: $responseBody;
    }

    public function isSuccessful($response): bool
    {
        if (! $response) {
            return false;
        }

        return (int) substr($response->getStatusCode(), 0, 1) === 2;
    }

    protected function handleRequestError(ResponseInterface $response): void
    {
        if ($response->getStatusCode() === 422) {
            throw new ValidationException(json_decode((string) $response->getBody(), true));
        }

        if ($response->getStatusCode() === 404) {
            throw new NotFoundException();
        }

        if ($response->getStatusCode() === 400) {
            throw new FailedActionException((string) $response->getBody());
        }

        if ($response->getStatusCode() === 401) {
            throw new UnauthorizedException((string) $response->getBody());
        }

        throw new Exception((string) $response->getBody());
    }
}
