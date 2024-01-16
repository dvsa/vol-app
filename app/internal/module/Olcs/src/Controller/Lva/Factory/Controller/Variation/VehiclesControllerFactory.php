<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\VehiclesController;
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
        $formHelperService = $container->get(FormHelperService::class);
        $formServiceManager =    $container->get(FormServiceManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $responseHelper = $container->get(ResponseHelperService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);
        $variationLvaService = $container->get(VariationLvaService::class);
        $goodeVehiclesVehicle = $container->get(GoodsVehiclesVehicle::class);
        $navigation = $container->get('Navigation');

        return new VehiclesController(
            $niTextTranslationUtil,
            $authService,
            $formHelperService,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $guidanceHelper,
            $translationHelper,
            $scriptFactory,
            $variationLvaService,
            $goodeVehiclesVehicle,
            $responseHelper,
            $stringHelper,
            $navigation
        );
    }
}
