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
class WithRefdataFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WithRefdata
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WithRefdata
    {
        return new WithRefdata(
            $container->get('doctrine.entitymanager.orm_default'),
            $container->get('QueryPartialServiceManager')->get('with')
        );
    }
}
