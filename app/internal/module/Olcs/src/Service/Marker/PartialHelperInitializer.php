<?php

namespace Olcs\Service\Marker;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

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
            $container->get('ViewHelperManager')->get('partial')
        );

        return $instance;
    }
}
