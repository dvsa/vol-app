<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\VehiclesPsvController;
use LmcRbacMvc\Service\AuthorizationService;

class VehiclesPsvControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return VehiclesPsvController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VehiclesPsvController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $urlHelper = $container->get(UrlHelperService::class);
        $responseHelper = $container->get(ResponseHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('navigation');

        return new VehiclesPsvController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $scriptFactory,
            $urlHelper,
            $responseHelper,
            $tableFactory,
            $translationHelper,
            $guidanceHelper,
            $stringHelper,
            $navigation
        );
    }
}
