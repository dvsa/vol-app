<?php

namespace Dvsa\Olcs\Cli\Service\Queue\Consumer\Factory;

use Dvsa\Olcs\Cli\Service\Queue\Consumer\AbstractConsumerServices;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractConsumerServicesFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AbstractConsumerServices(
            $container->get('CommandHandlerManager')
        );
    }
}
