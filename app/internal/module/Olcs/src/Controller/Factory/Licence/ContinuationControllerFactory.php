<?php

namespace Olcs\Controller\Factory\Licence;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Validator\Translator\TranslatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Licence\ContinuationController;

class ContinuationControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ContinuationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ContinuationController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslatorInterface::class);

        return new ContinuationController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ContinuationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ContinuationController
    {
        return $this->__invoke($serviceLocator, ContinuationController::class);
    }
}
