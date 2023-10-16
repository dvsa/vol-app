<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Controller\Dispatcher;
use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
use Common\FeatureToggle;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Olcs\Controller\Licence\Vehicle\SwitchBoardControllerFactory;
use Olcs\Controller\Lva\Licence\VehiclesController;
use ZfcRbac\Service\AuthorizationService;

class VehiclesControllerFactory extends BinaryFeatureToggleAwareControllerFactory
{
    /**
     * @return array
     */
    protected function getFeatureToggleNames(): array
    {
        return [
            FeatureToggle::DVLA_INTEGRATION
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function createServiceWhenEnabled(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        return (new SwitchBoardControllerFactory())->__invoke($container, $requestedName, $options);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return VehiclesController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null): VehiclesController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $tableFactory = $container->get(TableFactory::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $variationLvaService = $container->get(VariationLvaService::class);
        $goodsVehicleMapper = $container->get(GoodsVehiclesVehicle::class);
        $responseHelper = $container->get(ResponseHelperService::class);
        $licenceLvaAdapter = $container->get(LicenceLvaAdapter::class);

        return new VehiclesController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $guidanceHelper,
            $translationHelper,
            $scriptFactory,
            $variationLvaService,
            $goodsVehicleMapper,
            $responseHelper,
            $licenceLvaAdapter
        );
    }
}
