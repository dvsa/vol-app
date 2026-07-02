<?php

namespace Common\Data\Mapper\Lva;

use Common\Service\Table\Formatter\FormatterPluginManager;
use Common\Service\Table\Formatter\VehicleDiscNo;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GoodsVehiclesVehicleFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GoodsVehiclesVehicle
    {
        return new GoodsVehiclesVehicle(
            $container->get(FormatterPluginManager::class)->get(VehicleDiscNo::class)
        );
    }
}
