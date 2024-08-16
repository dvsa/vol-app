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
class WithUserFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WithUser
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WithUser
    {
        return new WithUser(
            $container->get('QueryPartialServiceManager')->get('with')
        );
    }
}
