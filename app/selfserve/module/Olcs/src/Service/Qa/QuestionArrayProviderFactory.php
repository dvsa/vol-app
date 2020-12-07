<?php

namespace Olcs\Service\Qa;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class QuestionArrayProviderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return QuestionArrayProvider
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new QuestionArrayProvider(
            $serviceLocator->get('QaFormattedTranslateableTextParametersGenerator')
        );
    }
}
