<?php

namespace Olcs\Service\Cookie;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DeleteSetCookieGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return DeleteSetCookieGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : DeleteSetCookieGenerator
    {
        return $this->__invoke($serviceLocator, DeleteSetCookieGenerator::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DeleteSetCookieGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : DeleteSetCookieGenerator
    {
        return new DeleteSetCookieGenerator(
            $container->get('CookieSetCookieFactory'),
            $container->get('CookieCookieExpiryGenerator')
        );
    }
}
