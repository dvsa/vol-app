<?php

namespace Admin\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DiscPrintingControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DiscPrintingController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $tableFactory = $container->get(TableFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formHelper = $container->get(FormHelperService::class);

        return new DiscPrintingController(
            $scriptFactory,
            $tableFactory,
            $flashMessengerHelper,
            $formHelper
        );
    }
    public function createService(ServiceLocatorInterface $serviceLocator): DiscPrintingController
    {
        $container = method_exists($serviceLocator, 'getServiceLocator') ? $serviceLocator->getServiceLocator() : $serviceLocator;

        return $this->__invoke($container, DiscPrintingController::class);
    }
}
