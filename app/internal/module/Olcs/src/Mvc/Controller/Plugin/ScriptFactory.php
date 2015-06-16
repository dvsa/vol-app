<?php

namespace Olcs\Mvc\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ScriptFactory
 * @package Olcs\Mvc\Controller\Plugin
 */
class ScriptFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Script($serviceLocator->getServiceLocator()->get('Script'));
    }
}
