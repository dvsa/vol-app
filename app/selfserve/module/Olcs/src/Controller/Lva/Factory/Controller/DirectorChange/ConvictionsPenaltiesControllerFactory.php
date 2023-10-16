<?php

namespace Olcs\Controller\Lva\Factory\Controller\DirectorChange;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\DirectorChange\ConvictionsPenaltiesController;
use ZfcRbac\Service\AuthorizationService;

class ConvictionsPenaltiesControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ConvictionsPenaltiesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConvictionsPenaltiesController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $tableFactory = $container->get(TableFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $flashMessengerPlugin = $container->get('ControllerPluginManager')->get('FlashMessenger');

        return new ConvictionsPenaltiesController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $translationHelper,
            $scriptFactory,
            $flashMessengerPlugin
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ConvictionsPenaltiesController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ConvictionsPenaltiesController
    {
        return $this->__invoke($serviceLocator, ConvictionsPenaltiesController::class);
    }
}
