<?php

namespace Dvsa\Olcs\Api\Mvc;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class OlcsBlameableListenerFactory
 *
 * @package Olcs\Api\Mvc
 */
class OlcsBlameableListenerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OlcsBlameableListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OlcsBlameableListener
    {
        return new OlcsBlameableListener($container);
    }
}
