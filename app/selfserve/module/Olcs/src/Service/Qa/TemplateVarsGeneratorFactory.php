<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
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
    public function createService(ServiceLocatorInterface $serviceLocator) : TemplateVarsGenerator
    {
        return $this->__invoke($serviceLocator, TemplateVarsGenerator::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TemplateVarsGenerator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : TemplateVarsGenerator
    {
        return new TemplateVarsGenerator(
            $container->get('QaQuestionArrayProvider'),
            $container->get('QaGuidanceTemplateVarsAdder')
        );
    }
}
