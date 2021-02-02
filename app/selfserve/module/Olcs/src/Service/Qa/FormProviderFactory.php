<?php

namespace Olcs\Service\Qa;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Form\Factory as FormFactory;

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
        $config = $serviceLocator->get('Config');

        return new FormProvider(
            $serviceLocator->get('QaFormFactory'),
            $serviceLocator->get('QaFieldsetPopulator'),
            new FormFactory(),
            $serviceLocator->get('FormAnnotationBuilder'),
            $config['qa']['submit_options']
        );
    }
}
