<?php

namespace Dvsa\Olcs\Db\Service\Search;

use Elastica\Client;
use Olcs\Logging\Log\Logger;
use Olcs\Logging\Log\LaminasLogPsr3Adapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class ClientFactory
 * @package Olcs\Db\Service\Search
 */
class ClientFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Client
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws Exception\InvalidServiceException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Client
    {
        $config = $container->get('config');
        if (!isset($config['elastic_search'])) {
            throw new Exception\InvalidServiceException('Elastic search config not found');
        }
        $service = new Client($config['elastic_search']);
        if (isset($config['elastic_search']['log'])) {
            $log = new LaminasLogPsr3Adapter(Logger::getLogger());
            $service->setLogger($log);
        }
        return $service;
    }
}
