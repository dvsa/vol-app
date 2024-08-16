<?php

namespace Dvsa\Olcs\Api\Service\Nysiis;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Http\Client as RestClient;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Psr\Container\ContainerInterface;

/**
 * Class NysiisRestClientFactory
 * @package Dvsa\Olcs\Api\Service\Nysiis
 */
class NysiisRestClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return NysiisRestClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NysiisRestClient
    {
        $config = $container->get('config');
        if (!isset($config['nysiis']['rest']['uri'])) {
            throw new \RuntimeException('Missing nysiis rest client uri');
        }
        if (!isset($config['nysiis']['rest']['options'])) {
            throw new \RuntimeException('Missing nysiis rest client options');
        }
        $httpClient = new RestClient($config['nysiis']['rest']['uri'], $config['nysiis']['rest']['options']);
        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);
        return new NysiisRestClient($httpClient);
    }
}
