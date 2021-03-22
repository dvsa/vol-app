<?php

namespace Olcs\Controller\Lva\Licence;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @see PeopleController
 */
class PeopleControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return PeopleController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PeopleController(
            $serviceLocator->get('translator')
        );
    }
}
