<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class EmissionsCategoryAvailabilityCounterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return EmissionsCategoryAvailabilityCounter
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EmissionsCategoryAvailabilityCounter
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new EmissionsCategoryAvailabilityCounter(
            $container->get('doctrine.connection.orm_default'),
            $repoServiceManager->get('IrhpPermitRange'),
            $repoServiceManager->get('IrhpPermitApplication'),
            $repoServiceManager->get('IrhpPermit'),
            $repoServiceManager->get('IrhpPermitStock'),
            $repoServiceManager->get('IrhpCandidatePermit')
        );
    }
}
