<?php

namespace Dovu\GuardianPhpSdk;

use Dovu\GuardianPhpSdk\HttpClient\HttpClient;
class BaseAPIClient
{
    /** @var string */
    public string $apiToken = "";

    public string $hmacSecret = "";

    public array $config = [];



    public function __construct()
    {
        $this->config = $this->getConfigFromFile();

        $this->base_uri = $this->config['app']['base_url'];
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
     * @param string $uri
     * @return void
     */
    protected function get(string $uri)
    {
        $client = HttpClient::get()
                    ->withBaseUri($this->base_uri)
                    ->withHmac($this->base_uri.$uri, [], $this->hmacSecret);

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
                    ->withBaseUri($this->base_uri)
                    ->withBody(['form_params' => $payload])
                    ->withHmac($this->base_uri.$uri, $payload, $this->hmacSecret);


        $client->setApiToken($this->apiToken);

        return $client->request($uri);
    }


    /**
     *
     * @param string $uri
     * @param [type] $payload
     * @return void
     */
    public function postJson(string $uri, $payload )
    {
        $client = HttpClient::post()
                    ->withBaseUri($this->base_uri)
                    ->withBody(['json' => $payload])
                    ->withHmac($this->base_uri.$uri, $payload, $this->hmacSecret);

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
                    ->withBaseUri($this->base_uri)
                    ->withBody(['form_params' => $payload])
                    ->withHmac($this->base_uri.$uri, $payload, $this->hmacSecret);

        $client->setApiToken($this->apiToken);
                    
        return $client->request($uri);

    }


    /**
     *
     * @param [type] $response
     * @return boolean
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
        return include "./config/app.php";
    }

}
