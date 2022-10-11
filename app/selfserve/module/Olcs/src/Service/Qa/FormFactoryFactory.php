<?php

namespace Olcs\Service\Qa;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class FormFactoryFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FormFactory
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : FormFactory
    {
        return $this->__invoke($serviceLocator, FormFactory::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @param null $
     * @return FormFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : FormFactory
    {
        return new FormFactory($container);
    }
}
