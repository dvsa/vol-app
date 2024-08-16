<?php

/**
 * With Contact Details Factory
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * With Contact Details Factory
 */
class WithContactDetailsFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WithContactDetails
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WithContactDetails
    {
        return new WithContactDetails(
            $container->get('doctrine.entitymanager.orm_default'),
            $container->get('QueryPartialServiceManager')->get('with'),
            $container->get('QueryPartialServiceManager')->get('withRefdata')
        );
    }
}
