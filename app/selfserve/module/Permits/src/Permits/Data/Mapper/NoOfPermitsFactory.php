<?php

namespace Permits\Data\Mapper;

use Interop\Container\ContainerInterface;
use Common\Data\Mapper\Permits\NoOfPermits as CommonNoOfPermits;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class NoOfPermitsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return NoOfPermits
     */
    public function createService(ServiceLocatorInterface $serviceLocator): NoOfPermits
    {
        return $this->__invoke($serviceLocator, NoOfPermits::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return NoOfPermits
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermits
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $mapperManager = $container->get(MapperManager::class);
        return new NoOfPermits(
            $mapperManager->get(CommonNoOfPermits::class)
        );
    }
}
