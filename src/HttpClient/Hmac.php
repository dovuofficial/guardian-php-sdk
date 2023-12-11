<?php

namespace Dovu\GuardianPhpSdk\HttpClient;

use Carbon\Carbon;

class Hmac
{
    private string $method;
    private string $hashedStr;
    private array  $url;
    private string $hashedBody;
    private string $signature;
    private static Hmac $instance;

    private ?string $date;

    public function create(
        string $method,
        string $url,
        array  $body = [],
        string $secret = ""
    ) {
        $this->method = strtoupper($method);

        $this->url = parse_url($url);

        $this->date = Carbon::now()->toISOString();

        $this->hashedBody = $this->hashBody($body);

        $this->hashedStr = $this->stringToSign();

        $this->signature = $this->createSignature($secret);
    }

    /**
     *
     * @return array
     */
    public function get(): array
    {
        return [
            'host' => $this->url['host'],
            'x-date' => $this->date,
            'x-content-sha256' => $this->hashedBody,
            'x-signature' => $this->signature,
        ];
    }

    public static function getInstance(): Hmac
    {
        if (empty(self::$instance)) {
            self::$instance = new Hmac();
        }

        return self::$instance;
    }

    /**
     *
     * @return string
     */
    private function stringToSign(): string
    {
        if (isset($this->url['port']) && ! in_array($this->url['port'], [80, 443])) {
            $this->url['host'] .= ':' . $this->url['port'];
        }

        $string = "{$this->method}\n{$this->url['path']}\n{$this->date};{$this->url['host']}";


        if (! empty($this->hashedBody)) {
            $string .= ";{$this->hashedBody}";
        }

        return $string;
    }

    /**
     *
     * @param array $body
     * @return string
     */
    private function hashBody(array $body = []): string
    {
        $body = empty($body) ? '{}' : json_encode($body, JSON_UNESCAPED_SLASHES);

        $hashedStr = hash('sha256', $body, true);

        return base64_encode($hashedStr);
    }

    /**
     *
     * @param string $secret
     * @return string
     */
    private function createSignature(string $secret): string
    {
        $hashhmac = hash_hmac('sha256', $this->hashedStr, $secret, true);

        return base64_encode($hashhmac);
    }
}
