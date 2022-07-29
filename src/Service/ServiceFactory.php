<?php

namespace Dovu\GuardianPhpSdk\Service;

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
        self::$classMap = $this->importServicesFromConfig();
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
     *
     * @return array<string, string>
     */
    public function importServicesFromConfig()
    {
        return (include('./config/app.php'))['services'];
    }

    /**
     * @param string $name
     * @return null|AbstractService
     */
    public function __get(string $name)
    {
        $serviceClass = $this->getServiceClass($name);

        if ($serviceClass !== null) {
            return new $serviceClass($this->client);
        }

        trigger_error('Undefined property: ' . static::class . '::$' . $name);

        return null;
    }
}
