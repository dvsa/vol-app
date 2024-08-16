<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicationFeesClearerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationFeesClearer
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationFeesClearer
    {
        return new ApplicationFeesClearer(
            $container->get('CqrsCommandCreator'),
            $container->get('CommandHandlerManager'),
            $container->get('RepositoryServiceManager')->get('Fee')
        );
    }
}
