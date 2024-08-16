<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class WindowAvailabilityCheckerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return WindowAvailabilityChecker
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WindowAvailabilityChecker
    {
        return new WindowAvailabilityChecker(
            $container->get('RepositoryServiceManager')->get('IrhpPermitWindow'),
            $container->get('PermitsAvailabilityStockAvailabilityChecker')
        );
    }
}
