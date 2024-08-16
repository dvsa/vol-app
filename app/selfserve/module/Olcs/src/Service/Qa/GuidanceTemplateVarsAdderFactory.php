<?php

namespace Olcs\Service\Qa;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GuidanceTemplateVarsAdderFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return GuidanceTemplateVarsAdder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GuidanceTemplateVarsAdder
    {
        return new GuidanceTemplateVarsAdder(
            $container->get('QaTranslateableTextHandler')
        );
    }
}
