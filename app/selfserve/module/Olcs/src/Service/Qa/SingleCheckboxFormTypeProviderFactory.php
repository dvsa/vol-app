<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SingleCheckboxFormTypeProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SingleCheckboxFormTypeProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SingleCheckboxFormTypeProvider(
            $serviceLocator->get('Helper\Form')
        );
    }
}
