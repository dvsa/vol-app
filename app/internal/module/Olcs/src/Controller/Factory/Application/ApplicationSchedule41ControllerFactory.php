<?php

namespace Olcs\Controller\Factory\Application;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Application\ApplicationSchedule41Controller;
use LmcRbacMvc\Service\AuthorizationService;

class ApplicationSchedule41ControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ApplicationSchedule41Controller
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationSchedule41Controller
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('navigation');
        $restrictionHelper = $container->get(RestrictionHelperService::class);

        return new ApplicationSchedule41Controller(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $tableFactory,
            $flashMessengerHelper,
            $stringHelper,
            $navigation,
            $restrictionHelper
        );
    }
}
