<?php

namespace Permits\Data\Mapper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class EcmtNoOfPermitsFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EcmtNoOfPermits
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : EcmtNoOfPermits
    {
        return $this->__invoke($serviceLocator, EcmtNoOfPermits::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return EcmtNoOfPermits
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : EcmtNoOfPermits
    {
        return new EcmtNoOfPermits(
            $container->get('Helper\Translation')
        );
    }
}
