<?php

namespace Common\Form\Element;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DynamicMultiCheckboxFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DynamicMultiCheckbox
    {
        $dataServiceManager = $container->get('DataServiceManager');
        return new DynamicMultiCheckbox($dataServiceManager);
    }
}
