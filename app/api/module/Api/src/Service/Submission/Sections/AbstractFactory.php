<?php

namespace Dvsa\Olcs\Api\Service\Submission\Sections;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AbstractFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        ;
        return new $requestedName($container->get('QueryHandlerManager'), $container->get('ViewRenderer'));
    }
}
