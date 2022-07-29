<?php

namespace Dovu\GuardianPhpSdk\Service;

class AbstractService
{
    public function __construct($client)
    {
        $this->client = $client;
    }
}
