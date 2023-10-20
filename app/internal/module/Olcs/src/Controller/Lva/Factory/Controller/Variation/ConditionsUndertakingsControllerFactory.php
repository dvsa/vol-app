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
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\ConditionsUndertakingsController;
use ZfcRbac\Service\AuthorizationService;

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
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $tableFactory = $container->get(TableFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $lvaAdapter = $container->get(VariationConditionsUndertakingsAdapter::class);
        $navigation = $container->get('Navigation');

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

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ConditionsUndertakingsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ConditionsUndertakingsController
    {
        return $this->__invoke($serviceLocator, ConditionsUndertakingsController::class);
    }
}
