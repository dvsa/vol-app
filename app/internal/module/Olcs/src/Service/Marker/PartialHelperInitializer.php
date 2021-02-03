<?php

namespace Olcs\Service\Marker;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\InitializerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class PartialHelperInitializer
 *
 * @package Dvsa\Olcs\Api\Service\Publication\Context
 */
class PartialHelperInitializer implements InitializerInterface
{
    /**
     * @param ContainerInterface $container
     * @param mixed $instance
     *
     * return mixed
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        $instance->setPartialHelper(
            $container->getServiceLocator()->get('ViewHelperManager')->get('partial')
        );

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, $instance);
    }
}
