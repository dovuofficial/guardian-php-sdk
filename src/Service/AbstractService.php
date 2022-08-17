<?php

namespace Dovu\GuardianPhpSdk\Service;

class AbstractService
{
    protected $client;

    protected $config = [];

    public function __construct($client)
    {
        $this->client = $client;
    }
}
