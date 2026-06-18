<?php

namespace Common\Controller\Lva;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Abstract Controller Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractControllerFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('Config');

        return isset($config['controllers']['lva_controllers'][$requestedName]);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');

        $class = $config['controllers']['lva_controllers'][$requestedName];

        $controller = new $class();

        if ($controller instanceof FactoryInterface) {
            return $controller->__invoke($container, $requestedName, $options);
        }

        return $controller;
    }
}
