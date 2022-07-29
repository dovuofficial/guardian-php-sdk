<?php

namespace Dovu\GuardianPhpSdk\HttpClient;

use GuzzleHttp\Client;
use Dovu\GuardianPhpSdk\Traits\MakesHttpRequests;

class HttpClient
{
    use MakesHttpRequests;

    /** @var string */
    public string $apiToken;

    public Client $client;

    private const API_URL = "http://localhost:3001/api/";

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;

        $this->client = new Client([
            'base_uri' => self::API_URL,
            'http_errors' => false,
            'headers' => [
                'Authorization' => "Bearer {$this->apiToken}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    protected function transformCollection(array $collection, string $class): array
    {
        return array_map(function ($attributes) use ($class) {
            return new $class($attributes, $this);
        }, $collection);
    }
}
