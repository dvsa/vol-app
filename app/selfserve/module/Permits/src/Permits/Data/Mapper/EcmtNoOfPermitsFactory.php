<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
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
    public function createService(ServiceLocatorInterface $serviceLocator): EcmtNoOfPermits
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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EcmtNoOfPermits
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        return new EcmtNoOfPermits(
            $container->get(TranslationHelperService::class)
        );
    }
}
