<?php

namespace Dovu\GuardianPhpSdk\Config;

use Dotenv\Dotenv;
use Dovu\GuardianPhpSdk\Constants\Env;

class EnvConfig
{
    public ?Dotenv $config = null;

    protected $array = [];

    private function __construct()
    {
        $this->config = Dotenv::createMutable(realpath('.'));
        $this->array = $this->config->load();
    }

    public static function instance()
    {
        return new self();
    }

    public function testsEnabled(): bool
    {
        return (bool) $this->get(Env::ALLOW_TESTS);
    }

    public function get(Env $item)
    {
        return $this->array[$item->value] ?? null;
    }

    public function hasStandardRegistry(): bool
    {
        return (bool) $this->get(Env::STANDARD_REGISTRY_USERNAME);
    }

    public function testLocalPolicy(): string|null
    {
        return $this->get(Env::POLICY_ID);
    }

    public function guardianApiUrl(): string|null
    {
        return $this->get(Env::GUARDIAN_API_URL);
    }
}
