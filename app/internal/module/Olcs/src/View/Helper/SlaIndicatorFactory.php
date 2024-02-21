<?php

namespace Olcs\View\Helper;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SlaIndicatorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SlaIndicator
    {
        return new SlaIndicator();
    }
}
