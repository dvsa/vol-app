<?php

namespace Dvsa\Olcs\Db\Service\Search;

use Elasticsearch\Client;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class SearchFactory
 * @package Olcs\Db\Service\Search
 */
class SearchFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Search
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Search
    {
        return new Search(
            $container->get(Client::class),
            $container->get(AuthorizationService::class),
            $container->get('RepositoryServiceManager')->get('SystemParameter'),
        );
    }
}
