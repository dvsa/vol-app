<?php

namespace Olcs\Service\Qa;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class TemplateVarsGeneratorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TemplateVarsGenerator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TemplateVarsGenerator(
            $serviceLocator->get('QaQuestionArrayProvider'),
            $serviceLocator->get('QaGuidanceTemplateVarsAdder')
        );
    }
}
