<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class AbstractFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return in_array(AbstractContext::class, class_parents($requestedName), true);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName($container->get('QueryHandlerManager'));
    }
}
