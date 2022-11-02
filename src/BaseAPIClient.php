<?php

namespace Dovu\GuardianPhpSdk;

use Dovu\GuardianPhpSdk\HttpClient\HttpClient;

class BaseAPIClient
{
    /** @var string */
    public string $apiToken = "";

    public string $hmacSecret = "";

    public bool $throwErrors = true;

    public array $config = [];

    public function __construct()
    {
        $this->config = $this->getConfigFromFile();
    }

    /**
     *
     * @param string $apiToken
     * @return void
     */
    public function setApiToken(string $apiToken)
    {
        $this->apiToken = $apiToken;
    }

    public function setGuardianBaseUrl(string $url)
    {
        $this->baseUrl = $url;
    }

    /**
     *
     * @param string $secret
     * @return void
     */
    public function setHmacSecret(string $secret)
    {
        $this->hmacSecret = $secret;
    }


    /**
     *
     * @param boolean $errors
     * @return void
     */
    public function setThrowErrors(bool $errors = true): void
    {
        $this->throwErrors = $errors;
    }

    /**
     *
     * @param string $uri
     * @return void
     */
    public function get(string $uri)
    {
        $client = HttpClient::get()
                    ->withBaseUri($this->baseUrl)
                    ->withHmac($this->baseUrl.$uri, [], $this->hmacSecret);

        $client->setApiToken($this->apiToken);

        return $client->request($uri);
    }

    /**
     *
     * @param string $uri
     * @param array $payload
     * @return void
     */
    public function post(string $uri, array $payload = [])
    {
        $client = HttpClient::post()
                    ->withBaseUri($this->baseUrl)
                    ->withBody(['form_params' => $payload])
                    ->withHmac($this->baseUrl.$uri, $payload, $this->hmacSecret);


        $client->setApiToken($this->apiToken);

        return $client->request($uri);
    }

    /**
     *
     * @param string $uri
     * @param [type] $payload
     * @return void
     */
    public function postJson(string $uri, $payload)
    {
        $client = HttpClient::post()
                    ->withBaseUri($this->baseUrl)
                    ->withBody(['json' => $payload])
                    ->withHmac($this->baseUrl.$uri, $payload, $this->hmacSecret);

        $client->setApiToken($this->apiToken);

        return $client->request($uri);
    }

    /**
     *
     * @param string $uri
     * @param array $payload
     * @return void
     */
    public function put(string $uri, array $payload = [])
    {
        $client = HttpClient::put()
                    ->withBaseUri($this->baseUrl)
                    ->withBody(['form_params' => $payload])
                    ->withHmac($this->baseUrl.$uri, $payload, $this->hmacSecret);

        $client->setApiToken($this->apiToken);

        return $client->request($uri);
    }

    /**
     *
     * @param [type] $response
     * @return bool
     */
    public function isSuccessful($response): bool
    {
        if (! $response) {
            return false;
        }

        return (int) substr($response->getStatusCode(), 0, 1) === 2;
    }

    /**
     *
     * @return void
     */
    private function getConfigFromFile()
    {
        $path = dirname(__DIR__, 1);

        return include $path . "/config/app.php";
    }
}
