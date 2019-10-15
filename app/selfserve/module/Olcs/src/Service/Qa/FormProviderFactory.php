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
        return new FormProvider(
            $serviceLocator->get('QaFormFactory'),
            $serviceLocator->get('QaFieldsetPopulator')
        );
    }
}
