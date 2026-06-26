<?php

namespace Common\Form\Element;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DynamicRadioHtmlFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DynamicRadioHtml
    {
        $dataServiceManager = $container->get('DataServiceManager');
        return new DynamicRadioHtml($dataServiceManager);
    }
}
