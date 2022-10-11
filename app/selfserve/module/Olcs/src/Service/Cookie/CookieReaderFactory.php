<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class CookieReaderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CookieReader
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : CookieReader
    {
        return $this->__invoke($serviceLocator, CookieReader::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CookieReader
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : CookieReader
    {
        return new CookieReader(
            $container->get('CookieCookieStateFactory'),
            $container->get('CookiePreferencesFactory')
        );
    }
}
