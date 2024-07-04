<?php

namespace Dovu\GuardianPhpSdk\Domain;

use Psr\Http\Message\ResponseInterface;

enum DataResponseType: string
{
    case NULL = "NULL";
    case ARRAY = "array";
    case BOOL = "boolean";
}

class HttpClientResponse
{
    public ?int $status_code = null;
    public ?string $reason = null;

    public array $data = [];

    public ?\Exception $exception = null;

    public static function empty(): self
    {
        return new self();
    }

    public static function exception(\Exception $e): self
    {
        return (new self())->setException($e);
    }

    public static function with(ResponseInterface $response): self
    {
        return self::empty()
            ->setData($response)
            ->setStatusCode($response)
            ->setReason($response);
    }

    public function setException(?\Exception $exception): HttpClientResponse
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function setData(ResponseInterface $response): self
    {
        $data = json_decode((string) $response->getBody(), true);
        $type = gettype($data);

        $this->data = match ($type) {
            DataResponseType::ARRAY->value => $data,
            DataResponseType::BOOL->value => [ 'data' => (bool) $data ],
            DataResponseType::NULL->value => [ 'data' => null ],
            default => throw new \Exception("'ResponseInterface Impl' returned bad data type of [$type]")
        };

        return $this;
    }

    public function data(): array
    {
        // TODO: This needs to be handled better/more defensively.
        if ($this->exception) {
            return [
                'exception' => $this->exception,
            ];
        }

        return $this->data;
    }

    public function setStatusCode(ResponseInterface $response): HttpClientResponse
    {
        $this->status_code = $response->getStatusCode();

        return $this;
    }

    public function setReason(ResponseInterface $response): HttpClientResponse
    {
        $this->reason = $response->getReasonPhrase();

        return $this;
    }
}
