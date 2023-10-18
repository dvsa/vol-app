<?php

namespace Olcs\Controller\Factory\TransportManager;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\TransportManager\TransportManagerController;
use Olcs\Controller\TransportManager\TransportManagerDocumentController;
use Olcs\Service\Data\DocumentSubCategory;

class TransportManagerDocumentControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return TransportManagerController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $navigation = $container->get('navigation');
        $docSubCategoryDataService = $container->get(DocumentSubCategory::class);

        return new TransportManagerDocumentController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelper,
            $translationHelper,
            $navigation,
            $docSubCategoryDataService
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TransportManagerController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TransportManagerController
    {
        return $this->__invoke($serviceLocator, TransportManagerController::class);
    }
}
