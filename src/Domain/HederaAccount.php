<?php

namespace Dovu\GuardianPhpSdk\Domain;

use Dovu\GuardianPhpSdk\Config\EnvConfig;
use Dovu\GuardianPhpSdk\Constants\Env;

class HederaAccount
{
    public string $account_id;
    public string $private_key;

    /**
     * @param string $account_id
     * @param string $private_key
     */
    public function __construct(string $account_id, string $private_key)
    {
        $this->account_id = $account_id;
        $this->private_key = $private_key;
    }

    public static function fromConfig(EnvConfig $env)
    {
        $account_id = $env->get(Env::HEDERA_ACCOUNT_ID);
        $private_key = $env->get(Env::HEDERA_PRIVATE_KEY);

        return new self($account_id, $private_key);
    }
}
