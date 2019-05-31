<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GuidanceTemplateVarsAdderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return GuidanceTemplateVarsAdder
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new GuidanceTemplateVarsAdder(
            $serviceLocator->get('QaTranslateableTextHandler')
        );
    }
}
