<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Controller\Dispatcher;
use Common\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\ServiceManager\FactoryInterface;
use Dvsa\Olcs\Application\Controller\AddVehiclesQuestionController;
use Common\FeatureToggle;
use Interop\Container\ContainerInterface;
use Dvsa\Olcs\Application\Controller\LvaVehicleController;
use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\Controller\Plugin\HandleQuery;
use Common\Form\FormValidator;
use Common\Controller\Plugin\HandleCommand;

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
        return [FeatureToggle::DVLA_INTEGRATION];
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
        $instance = new LvaVehicleController();
        if ($instance instanceof FactoryInterface) {
            $instance = $instance->createService($container);
        }
        return $instance;
    }
}
