<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FormProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FormProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $mappings = [
            'checkbox' => $serviceLocator->get('QaSingleCheckboxFormTypeProvider'),
            'radio' => $serviceLocator->get('QaRadioFormTypeProvider'),
            'text' => $serviceLocator->get('QaTextFormTypeProvider'),
        ];

        return new FormProvider($mappings);
    }
}
