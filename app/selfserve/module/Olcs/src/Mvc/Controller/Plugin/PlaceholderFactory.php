<?php

namespace Olcs\Mvc\Controller\Plugin;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PlaceholderFactory
 * @package Olcs\Mvc\Controller\Plugin
 */
class PlaceholderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Placeholder($serviceLocator->getServiceLocator()->get('ViewHelperManager')->get('placeholder'));
    }
}
