<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Address;

use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GetAddressFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GetAddress
    {
        $instance = new GetAddress(
            $container->get(AddressHelperService::class)
        );
        return $instance->__invoke($container, $requestedName, $options);
    }
}
