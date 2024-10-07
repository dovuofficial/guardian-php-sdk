<?php

namespace Dovu\GuardianPhpSdk\Domain;

class GuardianToken
{
    public function __construct(public object $token)
    {
    }

    public static function none(): self
    {
        return new self((object) [
           "adminId" => null,
           "tokenId" => ""
        ]);
    }


    /**
     * When "adminId" from parent obj is falsely then ignore "id()" fn in client.
     *
     * Usually this would indicate a "published" policy over a "dry run" state.
     *
     * @return bool
     */
    public function hasValidToken(): bool
    {
        return !!! $this->token->adminId;
    }

    public function id(): string
    {
        return $this->token->tokenId;
    }
}
