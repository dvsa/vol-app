<?php

namespace Dvsa\Olcs\Scanning\Controller;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DocumentControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DocumentController
    {
        $commandHandlerManager = $container->get('CommandHandlerManager');
        return new DocumentController($commandHandlerManager);
    }
}
