<?php

namespace Common\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HandleQueryFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): HandleQuery
    {
        $annotationBuilder = $container->get('TransferAnnotationBuilder');
        $queryService = $container->get('QueryService');

        return new HandleQuery($annotationBuilder, $queryService);
    }
}
