<?php

namespace Olcs\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CookieManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return CookieManager
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CookieManager
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $config = $container->get('Config');
        return new CookieManager($config);
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $services
     *
     * @return CookieManager
     */
    public function createService(ServiceLocatorInterface $services)
    {
        return $this->__invoke($services, CookieManager::class);
    }
}
