<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

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
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): GuidanceTemplateVarsAdder
    {
        return new GuidanceTemplateVarsAdder(
            $container->get('QaTranslateableTextHandler')
        );
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed|void
     * @deprecated Use __invoke instead.
     */
    public function createService(ServiceLocatorInterface $serviceLocator): GuidanceTemplateVarsAdder
    {
        return $this->__invoke($serviceLocator, GuidanceTemplateVarsAdder::class);
    }
}
