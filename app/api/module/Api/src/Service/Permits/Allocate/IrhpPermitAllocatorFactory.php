<?php

namespace Dvsa\Olcs\Api\Service\Permits\Allocate;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IrhpPermitAllocatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpPermitAllocator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpPermitAllocator
    {
        return new IrhpPermitAllocator(
            $container->get('RepositoryServiceManager')->get('IrhpPermit')
        );
    }
}
