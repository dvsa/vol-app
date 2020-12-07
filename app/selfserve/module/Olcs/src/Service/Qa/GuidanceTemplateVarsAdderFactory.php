<?php

namespace Olcs\Service\Qa;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
