<?php

namespace Olcs\Controller\Listener;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Service\AuthorizationService;

/**
 * Class NavigationFactory
 * @author Ian Lindsay<ian@hemera-business-services.co.uk>
 */
class NavigationFactory implements FactoryInterface
{
    /**
     * Create navigation listener
     *
     * @param ServiceLocatorInterface $serviceLocator service locator
     *
     * @return Navigation
     */
    public function createService(ServiceLocatorInterface $serviceLocator): Navigation
    {
        return $this->__invoke($serviceLocator, Navigation::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Navigation
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Navigation
    {
        return new Navigation(
            $container->get('navigation'),
            $container->get('QuerySender'),
            $container->get(AuthorizationService::class)->getIdentity()
        );
    }
}
