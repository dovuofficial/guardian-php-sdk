<?php

namespace Dovu\GuardianPhpSdk\Workflow;

class GuardianWorkflowConfiguration
{
    public array $config = [];

    private function __construct($name)
    {
        $this->config = include realpath('.') . "/config/$name.php";
    }

    public static function get($name): array
    {
        return (new self($name))->config;
    }
}
