<?php

namespace Dvsa\Olcs\Utils\Client;

use Laminas\Http\Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class HttpProxyClientFactory
 *
 * Creates a Http Client, for connecting to external URL's
 * Reasoning is that external URL's might go through a proxy server, this class allows that
 * config to be specified in one place
 *
 * @package Dvsa\Olcs\Utils\View\Factory\Helper
 */
class HttpExternalClientFactory implements FactoryInterface
{
    public const CONFIG_KEY = 'http_external';

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Client
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $client = new Client();
        $config = $container->get('config');
        if (!empty($config[self::CONFIG_KEY])) {
            $client->setOptions($config[self::CONFIG_KEY]);
        }
        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($client);
        // Disable logging reponse data by default
        $wrapper->setShouldLogData(false);
        return $client;
    }
}
