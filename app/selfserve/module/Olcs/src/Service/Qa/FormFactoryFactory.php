<?php

namespace Olcs\Service\Qa;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class FormFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FormFactory
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new FormFactory($serviceLocator);
    }
}
