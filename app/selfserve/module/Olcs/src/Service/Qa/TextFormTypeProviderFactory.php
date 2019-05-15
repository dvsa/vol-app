<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TextFormTypeProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TextFormTypeProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TextFormTypeProvider(
            $serviceLocator->get('Helper\Form'),
            $serviceLocator->get('QaValidatorsAdder')
        );
    }
}
