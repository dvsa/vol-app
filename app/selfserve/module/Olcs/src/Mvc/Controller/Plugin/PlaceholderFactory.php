<?php

namespace Olcs\Mvc\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class PlaceholderFactory
 * @package Olcs\Mvc\Controller\Plugin
 */
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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : Placeholder
    {
        return new Placeholder($container->get('ViewHelperManager')->get('placeholder'));
    }
}
