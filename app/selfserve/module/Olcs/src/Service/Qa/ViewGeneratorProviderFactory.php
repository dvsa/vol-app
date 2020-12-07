<?php

namespace Olcs\Service\Qa;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ViewGeneratorProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ViewGeneratorProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $viewGeneratorProvider = new ViewGeneratorProvider();

        $viewGeneratorProvider->registerViewGenerator(
            'permits/application/question',
            $serviceLocator->get('QaIrhpApplicationViewGenerator')
        );

        $viewGeneratorProvider->registerViewGenerator(
            'permits/application/ipa/question',
            $serviceLocator->get('QaIrhpPermitApplicationViewGenerator')
        );

        return $viewGeneratorProvider;
    }
}
