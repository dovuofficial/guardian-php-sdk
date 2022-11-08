<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\Contracts\HttpClientInterface;

class AbstractService
{
    public function __construct(protected HttpClientInterface $httpClient)
    {}
}
