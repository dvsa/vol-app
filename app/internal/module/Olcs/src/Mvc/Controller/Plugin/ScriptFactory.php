<?php

namespace Olcs\Mvc\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ScriptFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Script
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Script
    {
        return new Script($container->get('Script'));
    }
}
