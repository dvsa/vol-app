<?php

namespace Common\Form\Element;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DynamicRadioFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DynamicRadio
    {
        $dataServiceManager = $container->get('DataServiceManager');
        return new DynamicRadio($dataServiceManager);
    }
}
