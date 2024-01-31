<?php

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Application\OverviewController;
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
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');

        return new OverviewController(
            $niTextTranslationUtil,
            $authService,
            $stringHelper,
            $applicationOverviewHelper,
            $formHelper,
            $restrictionHelper,
            $flashMessengerHelper,
            $navigation
        );
    }
}
