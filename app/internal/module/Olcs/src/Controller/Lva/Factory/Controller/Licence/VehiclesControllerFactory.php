<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
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
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Licence\VehiclesController;
use LmcRbacMvc\Service\AuthorizationService;

class VehiclesControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return VehiclesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VehiclesController
    {
        
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
        $navigation = $container->get('Navigation');

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
            $navigation
        );
    }
}
