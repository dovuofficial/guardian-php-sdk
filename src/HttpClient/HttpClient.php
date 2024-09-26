<?php

namespace Dovu\GuardianPhpSdk\HttpClient;

use Dovu\GuardianPhpSdk\Constants\HttpMethod;
use Dovu\GuardianPhpSdk\Contracts\HttpClientInterface;
use Dovu\GuardianPhpSdk\Domain\HttpClientResponse;
use Dovu\GuardianPhpSdk\Exceptions\FailedActionException;
use Dovu\GuardianPhpSdk\Exceptions\NotFoundException;
use Dovu\GuardianPhpSdk\Exceptions\UnauthorizedException;
use Dovu\GuardianPhpSdk\Exceptions\ValidationException;
use Dovu\GuardianPhpSdk\Notifications\NotificationManager;
use Exception;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class HttpClient implements HttpClientInterface
{
    protected array $hmac = [];
    private array $body;
    private string $apiToken;
    private bool $throwErrors;
    private string $hmacSecret;
    private string $baseUrl;
    private Client $client;

    private HttpMethod $method;

    public function __construct(private $settings)
    {
        $this->baseUrl = $settings->baseUrl;
        $this->apiToken = $settings->apiToken;
        $this->throwErrors = $settings->throwErrors;
        $this->hmacSecret = $settings->hmacSecret;

        $this->client = new Client([
            'base_uri' => $settings->baseUrl,
            'http_errors' => false,
            'debug' => false,
        ]);
    }

    public function request(string $uri): HttpClientResponse
    {
        $payload = $this->body ?? null;

        if (! empty($this->hmac)) {
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
            strtoupper($this->method->value),
            $uri,
            $payload
        );

        try {
            if (! $this->isSuccessful($response) && $this->throwErrors) {
                $this->handleRequestError($response);
            }

            return HttpClientResponse::with($response);

        } catch (\Exception $e) {
            $notificationManager = new NotificationManager($this->settings);
            $notificationManager->register($e);

            return HttpClientResponse::exception($e);
        }
    }

    //    public function get(string $uri): bool|array|Exception|null
    public function get(string $uri): HttpClientResponse
    {
        $this->method = HttpMethod::GET;

        $this->hmac = $this->setHmac($uri);

        return $this->request($uri);
    }

    public function post(string $uri, array $payload = [], bool $jsonRequest = false): HttpClientResponse
    {
        $this->method = HttpMethod::POST;

        $this->body = ['form_params' => $payload];

        if ($jsonRequest) {
            $this->body = ['json' => $payload];
        }

        $this->hmac = $this->setHmac($uri, $payload);

        return $this->request($uri);
    }

    public function put(string $uri, array $payload = [], bool $jsonRequest = false): HttpClientResponse
    {
        $this->method = HttpMethod::PUT;

        $this->body = ['form_params' => $payload];

        if ($jsonRequest) {
            $this->body = ['json' => $payload];
        }

        $this->hmac = $this->setHmac($uri, $payload);

        return $this->request($uri);
    }

    private function setHmac(string $url, array $body = []): array
    {
        $hmac = Hmac::getInstance();
        $hmac->create($this->method->value, $this->baseUrl.$url, $body, $this->hmacSecret);

        return $hmac->get();
    }

    private function isSuccessful($response): bool
    {
        if (! $response) {
            return false;
        }

        return (int) substr($response->getStatusCode(), 0, 1) === 2;
    }

    private function handleRequestError(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === 422) {
            throw new ValidationException(json_decode((string) $response->getBody(), true));
        }

        if ($statusCode === 404) {
            throw new NotFoundException((string) $response->getBody(), $statusCode);
        }

        if ($statusCode === 400) {
            throw new FailedActionException((string) $response->getBody(), $statusCode);
        }

        if ($statusCode === 401) {
            throw new UnauthorizedException((string) $response->getBody(), $statusCode);
        }

        throw new Exception((string) $response->getBody(), $statusCode);
    }
}
