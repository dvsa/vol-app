<?php

namespace Olcs\Mvc\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PlaceholderFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Placeholder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Placeholder
    {
        return new Placeholder($container->get('ViewHelperManager')->get('placeholder'));
    }
}
