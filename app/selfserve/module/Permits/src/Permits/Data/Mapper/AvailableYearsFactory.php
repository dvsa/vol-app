<?php

namespace Permits\Data\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AvailableYearsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AvailableYears
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : AvailableYears
    {
        return $this->__invoke($serviceLocator, AvailableYears::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AvailableYears
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : AvailableYears
    {
        return new AvailableYears(
            $container->get('Helper\Translation')
        );
    }
}
