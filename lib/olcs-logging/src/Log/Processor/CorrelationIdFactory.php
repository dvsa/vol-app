<?php

namespace Olcs\Logging\Log\Processor;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CorrelationIdFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     *
     * @return CorrelationId
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CorrelationId
    {
        return new CorrelationId(
            $container->get('Request')
        );
    }
}
