<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TemplateVarsGeneratorFactory implements FactoryInterface
{
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
