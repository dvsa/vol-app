<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

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
            $repoServiceManager->get('IrhpPermitRange'),
            $repoServiceManager->get('IrhpPermit'),
            $repoServiceManager->get('IrhpApplication')
        );
    }
}
