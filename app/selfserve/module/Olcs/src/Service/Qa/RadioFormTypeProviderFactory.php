<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RadioFormTypeProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RadioFormTypeProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RadioFormTypeProvider(
            $serviceLocator->get('Helper\Form')
        );
    }
}
