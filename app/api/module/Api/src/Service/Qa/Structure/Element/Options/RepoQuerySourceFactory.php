<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RepoQuerySourceFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RepoQuerySource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RepoQuerySource
    {
        return new RepoQuerySource(
            $container->get('RepositoryServiceManager')
        );
    }
}
