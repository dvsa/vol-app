<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Controller\Dispatcher;
use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
use Common\FeatureToggle;
use Common\Form\FormValidator;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Application\Controller\AddVehiclesQuestionController;
use Dvsa\Olcs\Application\Controller\LvaVehicleController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Controller\Plugin\Url;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see AddVehiclesQuestionController
 */
class AddVehiclesQuestionControllerFactory extends BinaryFeatureToggleAwareControllerFactory
{
    /**
     * @inheritDoc
     */
    protected function getFeatureToggleNames(): array
    {
        return [FeatureToggle::NEW_GOODS_APP_VEHICLE];
    }

    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return Dispatcher
     */
    protected function createServiceWhenEnabled(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        $controllerPluginManager = $container->get('ControllerPluginManager');

        $controller = new AddVehiclesQuestionController(
            $urlHelper = $controllerPluginManager->get(Url::class),
            $redirectHelper = $controllerPluginManager->get(Redirect::class),
            $controllerPluginManager->get(HandleQuery::class),
            $controllerPluginManager->get('FlashMessenger'),
            $container->get(FormValidator::class),
            $commandHandler = $controllerPluginManager->get(HandleCommand::class)
        );

        // Decorate controller
        $instance = new Dispatcher($controller);

        // Initialize plugins
        $urlHelper->setController($instance);
        $redirectHelper->setController($instance);
        $commandHandler->setController($instance);

        return $instance;
    }

    /**
     * @param ContainerInterface $container
     * @param mixed $requestedName
     * @param array|null $options
     * @return LvaVehicleController
     */
    protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null): LvaVehicleController
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
        $goodsVehiclesVehicleMapper = $container->get(GoodsVehiclesVehicle::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);

        return new LvaVehicleController(
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
            $goodsVehiclesVehicleMapper,
            $restrictionHelper,
            $stringHelper
        );
    }
}
