<?php

namespace Dovu\GuardianPhpSdk;

// use Dovu\GuardianPhpSdk\HttpClient\HttpClient;

class BaseAPIClient
{
    public string $apiToken = "";
    public string $hmacSecret = "";
    public string $baseUrl;
    public bool $throwErrors = true;
    public array $notifications = [];
    public array $config = [];

    public function __construct()
    {
        $this->config = $this->getConfigFromFile();
    }

    public function setApiToken(string $apiToken): void
    {
        $this->apiToken = $apiToken;
    }

    public function setGuardianBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    public function setHmacSecret(string $secret): void
    {
        $this->hmacSecret = $secret;
    }

    public function setThrowErrors(bool $errors = true): void
    {
        $this->throwErrors = $errors;
    }

    public function addNotification(array $notification)
    {
        $this->notifications[] = $notification;
    }

    private function getConfigFromFile(): array
    {
        $path = dirname(__DIR__, 1);

        return include $path . "/config/app.php";
    }
}
