<?php

/**
 * With Refdata Factory
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * With Refdata Factory
 */
class WithBusRegFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WithBusReg
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WithBusReg
    {
        return new WithBusReg(
            $container->get('QueryPartialServiceManager')->get('with')
        );
    }
}
