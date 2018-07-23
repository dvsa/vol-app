<?php

namespace Olcs\Controller\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
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
        return new Navigation(
            $serviceLocator->get('navigation'),
            $serviceLocator->get('QuerySender'),
            $serviceLocator->get(AuthorizationService::class)->getIdentity()
        );
    }
}
