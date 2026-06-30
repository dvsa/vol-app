<?php

namespace Common\Form\Element;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DynamicSelectFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DynamicSelect
    {
        $dataServiceManager = $container->get('DataServiceManager');
        return new DynamicSelect($dataServiceManager);
    }
}
