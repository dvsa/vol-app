<?php

/**
 * WithPersonContactDetails Factory
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * WithPersonContactDetails Factory
 */
class WithPersonContactDetailsFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WithPersonContactDetails
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WithPersonContactDetails
    {
        return new WithPersonContactDetails(
            $container->get('QueryPartialServiceManager')->get('with')
        );
    }
}
