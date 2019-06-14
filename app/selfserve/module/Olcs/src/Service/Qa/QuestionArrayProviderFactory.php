<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
