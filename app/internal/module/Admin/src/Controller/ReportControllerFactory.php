<?php

namespace Admin\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper\Placeholder;

class ReportControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ReportController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $placeholder = $container->get('ViewHelperManager')->get(Placeholder::class);

        return new ReportController(
            $scriptFactory,
            $tableFactory,
            $formHelper,
            $placeholder
        );
    }
    public function createService(ServiceLocatorInterface $serviceLocator): ReportController
    {
        $container = method_exists($serviceLocator, 'getServiceLocator') ? $serviceLocator->getServiceLocator() : $serviceLocator;

        return $this->__invoke(
            $container,
            ReportController::class
        );
    }
}
