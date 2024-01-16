<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\OverviewController;
use Olcs\Service\Helper\ApplicationOverviewHelperService;
use LmcRbacMvc\Service\AuthorizationService;

class OverviewControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return OverviewController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OverviewController
    {
        
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $applicationOverviewHelper = $container->get(ApplicationOverviewHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $navigation = $container->get('Navigation');
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new OverviewController(
            $niTextTranslationUtil,
            $authService,
            $applicationOverviewHelper,
            $stringHelper,
            $formHelper,
            $formServiceManager,
            $navigation,
            $flashMessengerHelper
        );
    }
}
