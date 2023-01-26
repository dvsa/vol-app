<?php

namespace Olcs\Mvc\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class PlaceholderFactory
 * @package Olcs\Mvc\Controller\Plugin
 */
class PlaceholderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : Placeholder
    {
        return $this->__invoke($serviceLocator, Placeholder::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Placeholder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Placeholder
    {
        return new Placeholder($container->getServiceLocator()->get('ViewHelperManager')->get('placeholder'));
    }
}
