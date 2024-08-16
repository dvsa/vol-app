<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CandidatePermitsAvailableCountCalculatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CandidatePermitsAvailableCountCalculator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CandidatePermitsAvailableCountCalculator
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new CandidatePermitsAvailableCountCalculator(
            $repoServiceManager->get('IrhpCandidatePermit'),
            $repoServiceManager->get('IrhpPermit')
        );
    }
}
