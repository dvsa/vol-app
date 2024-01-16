<?php

namespace Olcs\Controller\Bus\Registration;

use Common\Service\Helper\FlashMessengerHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BusRegistrationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusRegistrationController
    {
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        assert($flashMessengerHelper instanceof FlashMessengerHelperService);

        return new BusRegistrationController(
            $flashMessengerHelper
        );
    }
}
