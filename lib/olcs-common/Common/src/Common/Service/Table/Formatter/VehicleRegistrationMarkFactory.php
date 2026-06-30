<?php

namespace Common\Service\Table\Formatter;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class VehicleRegistrationMarkFactory implements FactoryInterface
{
    /**
     * @param  $requestedName
     * @param  array|null         $options
     * @return VehicleRegistrationMark
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $translator = $container->get('translator');
        return new VehicleRegistrationMark($translator);
    }
}
