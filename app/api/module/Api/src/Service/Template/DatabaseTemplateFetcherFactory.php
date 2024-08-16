<?php

namespace Dvsa\Olcs\Api\Service\Template;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DatabaseTemplateFetcherFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DatabaseTemplateFetcher
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DatabaseTemplateFetcher
    {
        return new DatabaseTemplateFetcher(
            $container->get('RepositoryServiceManager')->get('Template')
        );
    }
}
