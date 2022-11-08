<?php

namespace Dovu\GuardianPhpSdk\Service;

use Dovu\GuardianPhpSdk\HttpClient\HttpClient;

/**
 * Service factory class for API resources.
 *
 * @property AccountService $accounts
 */
class ServiceFactory extends AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static array $classMap = [];

    public function __construct($client)
    {
        $this->client = $client;
        self::$classMap = $client->config['services'];
    }

    /**
     *
     * @param string $name
     * @return void
     */
    protected function getServiceClass($name)
    {
        return array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }

    /**
     * @param string $name
     * @return null
     */
    public function __get(string $name)
    {
        $serviceClass = $this->getServiceClass($name);

        if ($serviceClass !== null) {
            return new $serviceClass(new HttpClient($this->client));
        }

        trigger_error('Undefined property: ' . static::class . '::$' . $name);

        return null;
    }
}
