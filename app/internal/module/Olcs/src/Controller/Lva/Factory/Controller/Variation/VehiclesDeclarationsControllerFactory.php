<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\VehiclesDeclarationsController;
use LmcRbacMvc\Service\AuthorizationService;

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

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $dataHelper = $container->get(DataHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('navigation');
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new VehiclesDeclarationsController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $dataHelper,
            $stringHelper,
            $navigation,
            $flashMessengerHelper
        );
    }
}
