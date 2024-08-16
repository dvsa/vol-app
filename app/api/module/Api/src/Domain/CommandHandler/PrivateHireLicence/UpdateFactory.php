<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\PrivateHireLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactioningCommandHandler;
use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UpdateFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransactioningCommandHandler
    {
        $instance = new Update(
            $container->get(AddressHelperService::class)
        );
        return $instance->__invoke($container, $requestedName, $options);
    }
}
