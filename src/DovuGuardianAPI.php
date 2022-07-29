<?php

namespace Dovu\GuardianPhpSdk;

use Dovu\GuardianPhpSdk\Service\ServiceFactory;

class DovuGuardianAPI extends BaseAPIClient
{
    /**
     * @var serviceFactory
    */
    private $serviceFactory;

    public function __get(string $name)
    {
        $this->serviceFactory ??= new ServiceFactory($this);

        return $this->serviceFactory->__get($name);
    }
}
