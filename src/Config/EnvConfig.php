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
        return $this->array[$item->value];
    }
}
