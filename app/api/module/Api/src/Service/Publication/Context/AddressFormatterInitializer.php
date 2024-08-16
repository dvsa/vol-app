<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context;

use Dvsa\Olcs\Api\Service\Helper\AddressFormatterAwareInterface;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

/**
 * Class AddressFormatterInitializer
 *
 * @package Dvsa\Olcs\Api\Service\Publication\Context
 */
class AddressFormatterInitializer implements InitializerInterface
{
    /**
     * @param ContainerInterface $container
     * @param mixed $instance
     *
     * return mixed
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof AddressFormatterAwareInterface) {
            $instance->setAddressFormatter($container->get('AddressFormatter'));
        }

        return $instance;
    }
}
