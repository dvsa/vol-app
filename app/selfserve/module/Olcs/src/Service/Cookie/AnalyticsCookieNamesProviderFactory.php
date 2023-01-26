<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AnalyticsCookieNamesProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AnalyticsCookieNamesProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : AnalyticsCookieNamesProvider
    {
        return $this->__invoke($serviceLocator, AnalyticsCookieNamesProvider::class);
    }

    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return AnalyticsCookieNamesProvider
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : AnalyticsCookieNamesProvider
    {
        $config = $container->get('Config');
        return new AnalyticsCookieNamesProvider($config['google-ga-domain']);
    }
}
