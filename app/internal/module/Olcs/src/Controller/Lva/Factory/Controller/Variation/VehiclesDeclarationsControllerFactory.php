<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\VehiclesDeclarationsController;
use ZfcRbac\Service\AuthorizationService;

class VehiclesDeclarationsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return VehiclesDeclarationsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VehiclesDeclarationsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $dataHelper = $container->get(DataHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('Navigation');

        return new VehiclesDeclarationsController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $dataHelper,
            $stringHelper,
            $navigation
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return VehiclesDeclarationsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): VehiclesDeclarationsController
    {
        return $this->__invoke($serviceLocator, VehiclesDeclarationsController::class);
    }
}
