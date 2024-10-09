<?php

namespace Dovu\GuardianPhpSdk\Domain;

class TrustchainQuery
{
    public int $tries = 5;
    public int $attempts = 0;
    public int $wait = 5;

    /**
     * Include the result of the query after processing.
     *
     * @var CredentialDocumentBlock|null
     */
    public CredentialDocumentBlock|null $result = null;

    public function __construct(public string $uuid)
    {
    }

    public static function uuid(string $uuid): self
    {
        return new self($uuid);
    }

    public function canAttemptQuery(): bool
    {
        return $this->attempts < $this->tries;
    }

    public function setTries(int $tries): TrustchainQuery
    {
        $this->tries = $tries;

        return $this;
    }

    public function setWait(int $wait): TrustchainQuery
    {
        $this->wait = $wait;

        return $this;
    }

    public function defer(): void
    {
        ++$this->attempts;

        sleep($this->wait);
    }

    public function asTrustChain(): Trustchain
    {
        if ($this->hasTrustchainResult()) {
            return new Trustchain($this->result);
        }

        throw new \Exception("Unable to format empty result as Trustchain");
    }

    public function withResult(?CredentialDocumentBlock $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function hasTrustchainResult(): bool
    {
        return !! $this->result;
    }
}
