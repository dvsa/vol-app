<?php

namespace Olcs\Service\Qa;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
