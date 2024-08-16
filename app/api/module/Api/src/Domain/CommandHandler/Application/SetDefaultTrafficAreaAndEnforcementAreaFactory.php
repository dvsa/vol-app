<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SetDefaultTrafficAreaAndEnforcementAreaFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransactioningCommandHandler
    {
        $instance = new SetDefaultTrafficAreaAndEnforcementArea(
            $container->get(AddressHelperService::class)
        );
        return $instance->__invoke($container, $requestedName, $options);
    }
}
