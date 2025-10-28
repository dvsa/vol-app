<?php

namespace Olcs\Service\Data;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Factory for LetterType Data Service
 */
class LetterTypeFactory implements FactoryInterface
{
    /**
     * Invoke
     *
     * @param ContainerInterface $container Container
     * @param string $requestedName Requested name
     * @param array|null $options Options
     * @return LetterType
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new LetterType(
            $container->get('QueryService')
        );
    }
}
