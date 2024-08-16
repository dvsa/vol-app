<?php

namespace Dvsa\Olcs\Api\Service\Qa;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class QaContextGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return QaContextGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): QaContextGenerator
    {
        return new QaContextGenerator(
            $container->get('RepositoryServiceManager')->get('ApplicationStep'),
            $container->get('QaEntityProvider'),
            $container->get('QaContextFactory')
        );
    }
}
