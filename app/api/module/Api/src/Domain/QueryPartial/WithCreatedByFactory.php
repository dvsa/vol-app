<?php

/**
 * With CreatedBy Factory
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * With CreatedBy Factory
 */
class WithCreatedByFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WithCreatedBy
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WithCreatedBy
    {
        return new WithCreatedBy(
            $container->get('QueryPartialServiceManager')->get('with')
        );
    }
}
