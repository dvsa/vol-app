<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\Controller\Lva\Adapters\VariationConditionsUndertakingsAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Lva\Variation\ConditionsUndertakingsController;
use LmcRbacMvc\Service\AuthorizationService;

class ConditionsUndertakingsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return ConditionsUndertakingsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConditionsUndertakingsController
    {

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $tableFactory = $container->get(TableFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $lvaAdapter = $container->get(VariationConditionsUndertakingsAdapter::class);
        $navigation = $container->get('navigation');

        return new ConditionsUndertakingsController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $stringHelper,
            $lvaAdapter,
            $navigation
        );
    }
}
