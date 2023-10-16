<?php

namespace Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\Placeholder;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IndexController
    {
        $placeholder = $container->get('ViewHelperManager')->get(Placeholder::class);

        return new IndexController(
            $placeholder
        );
    }
    public function createService(ServiceLocatorInterface $serviceLocator): IndexController
    {
        $container = method_exists($serviceLocator, 'getServiceLocator') ? $serviceLocator->getServiceLocator() : $serviceLocator;

        return $this->__invoke($container, IndexController::class);
    }
}
