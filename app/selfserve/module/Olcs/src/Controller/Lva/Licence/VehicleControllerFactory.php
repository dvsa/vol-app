<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Dispatcher;
use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\FeatureToggle;
use Interop\Container\ContainerInterface;
use Olcs\Controller\Licence\Vehicle\SwitchBoardControllerFactory;

class VehicleControllerFactory extends BinaryFeatureToggleAwareControllerFactory
{
    /**
     * @return array
     */
    protected function getFeatureToggleNames(): array
    {
        return [
            FeatureToggle::DVLA_INTEGRATION
        ];
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Dispatcher
     */
    protected function createServiceWhenEnabled(ContainerInterface $container, $requestedName, array $options = null): Dispatcher
    {
        return (new SwitchBoardControllerFactory())->__invoke($container, $requestedName, $options);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return VehiclesController
     */
    protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null): VehiclesController
    {
        return new VehiclesController();
    }
}
