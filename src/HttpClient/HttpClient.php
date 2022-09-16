<?php

namespace Dovu\GuardianPhpSdk\HttpClient;

use Dovu\GuardianPhpSdk\Exceptions\FailedActionException;
use Dovu\GuardianPhpSdk\Exceptions\NotFoundException;
use Dovu\GuardianPhpSdk\Exceptions\UnauthorizedException;
use Dovu\GuardianPhpSdk\Exceptions\ValidationException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    protected array $hmac = [];

    public function __construct(private string $method)
    {
    }

    /**
     * @param string $uri
     * @return void
     */
    public function request(string $uri)
    {
        $payload = $this->body ?? null;

        if (!empty($this->hmac)) {
            $payload['headers'] = [
                'x-date' => $this->hmac['x-date'],
                'x-signature' => $this->hmac['x-signature'],
                'x-content-sha256' => $this->hmac['x-content-sha256'],
            ];
        }

        if (! empty($this->apiToken)) {
            $payload['headers']['Authorization'] = "Bearer {$this->apiToken}";
        }


        $response = $this->client->request(
            strtoupper($this->method),
            $uri,
            $payload
        );

        if (! $this->isSuccessful($response)) {
            return $response;
        }

        $responseBody = (string) $response->getBody();

        return json_decode($responseBody, true) ?: $responseBody;
      
    }

    /**
     *
     * @param string $token
     * @return void
     */
    public function setApiToken(string $token): void
    {
        $this->apiToken = $token;
    }

    /**
     *
     * @param string $uri
     * @return self
     */
    public function withBaseUri(string $uri): self
    {
        $this->client = new Client([
            'base_uri' => $uri,
            'http_errors' => false,
            'debug' => false,
        ]);

        return $this;
    }

    /**
     *
     * @param array $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     *
     * @param array $body
     * @return self
     */
    public function withBody(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     *
     * @param string $url
     * @param array $body
     * @param string $secret
     * @return self
     */
    public function withHmac(string $url, array $body, string $secret): self
    {
        $this->hmac = (new Hmac($this->method, $url, $body, $secret))->get();

        return $this;
    }

    /**
     *
     * @param [type] $response
     * @return bool
     */
    private function isSuccessful($response): bool
    {
        if (! $response) {
            return false;
        }

        return (int) substr($response->getStatusCode(), 0, 1) === 2;
    }

    /**
     *
     * @param ResponseInterface $response
     * @return void
     */
    private function handleRequestError(ResponseInterface $response): void
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

    /**
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        return new HttpClient($method);
    }
}
