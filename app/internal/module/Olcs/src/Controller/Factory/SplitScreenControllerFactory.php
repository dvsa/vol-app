<?php

namespace Olcs\Controller\Factory;

use Common\Service\Script\ScriptFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\SplitScreenController;

class SplitScreenControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return SplitScreenController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SplitScreenController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);

        return new SplitScreenController(
            $scriptFactory
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SplitScreenController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): SplitScreenController
    {
        return $this->__invoke($serviceLocator, SplitScreenController::class);
    }
}
