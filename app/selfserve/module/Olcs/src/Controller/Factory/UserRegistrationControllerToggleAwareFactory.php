<?php

namespace Olcs\Controller\Factory;

use Common\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactory;
use Common\FeatureToggle;
use Olcs\Controller\OperatorRegistrationController;
use Olcs\Controller\UserRegistrationController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class UserRegistrationControllerToggleAwareFactory extends BinaryFeatureToggleAwareControllerFactory
{
    protected function getFeatureToggleNames(): array
    {
        return [
            FeatureToggle::TRANSPORT_CONSULTANT_ROLE
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createServiceWhenEnabled(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $container->get('ControllerManager')->get(OperatorRegistrationController::class);


    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $container->get('ControllerManager')->get(UserRegistrationController::class);
    }
}
