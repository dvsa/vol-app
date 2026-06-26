<?php

namespace Common\Service\Section\VehicleSafety\Vehicle\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VrmFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return Vrm
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $viewHelperManager = $container->get('ViewHelperManager');
        return new Vrm($viewHelperManager);
    }
}
