<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AcceptAllSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed|void
     * @deprecated Use __invoke instead.
     */
    public function createService(ServiceLocatorInterface $serviceLocator): AcceptAllSetCookieGenerator
    {
        return $this->__invoke($serviceLocator, AcceptAllSetCookieGenerator::class);
    }

    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return AcceptAllSetCookieGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : AcceptAllSetCookieGenerator
    {
        return new AcceptAllSetCookieGenerator(
            $container->get('CookiePreferencesSetCookieGenerator'),
            $container->get('CookiePreferencesFactory')
        );
    }
}
