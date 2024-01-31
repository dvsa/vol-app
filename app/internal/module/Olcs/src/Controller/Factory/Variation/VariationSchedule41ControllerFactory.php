<?php

namespace Olcs\Controller\Factory\Variation;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Variation\VariationSchedule41Controller;
use LmcRbacMvc\Service\AuthorizationService;

class VariationSchedule41ControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return VariationSchedule41Controller
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationSchedule41Controller
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('navigation');

        return new VariationSchedule41Controller(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $tableFactory,
            $flashMessengerHelper,
            $stringHelper,
            $navigation
        );
    }
}
