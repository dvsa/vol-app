<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Address;

use Dvsa\Olcs\Api\Service\AddressHelper\AddressHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GetListFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GetList
    {
        $instance = new GetList(
            $container->get(AddressHelperService::class)
        );
        return $instance->__invoke($container, $requestedName, $options);
    }
}
