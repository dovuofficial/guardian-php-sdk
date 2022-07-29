<?php

namespace Dovu\GuardianPhpSdk\Service;

abstract class AbstractServiceFactory
{
    private $client;
    private $services;

    public function __construct($client)
    {
        $this->client = $client;
        $this->services = [];
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    abstract protected function getServiceClass($name);

    /**
     * @param string $name
     *
     * @return null|AbstractService|AbstractServiceFactory
     */
    public function __get(string $name)
    {
        $serviceClass = $this->getServiceClass($name);

        if ($serviceClass !== null) {
            if (! array_key_exists($name, $this->services)) {
                $this->services[$name] = new $serviceClass($this->client);
            }

            return $this->services[$name];
        }

        trigger_error('Undefined property: ' . static::class . '::$' . $name);

        return null;
    }
}
