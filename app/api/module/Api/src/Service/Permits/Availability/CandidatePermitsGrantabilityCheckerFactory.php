<?php

namespace Dvsa\Olcs\Api\Service\Permits\Availability;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CandidatePermitsGrantabilityCheckerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CandidatePermitsGrantabilityChecker
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CandidatePermitsGrantabilityChecker
    {
        return new CandidatePermitsGrantabilityChecker(
            $container->get('PermitsAvailabilityCandidatePermitsAvailableCountCalculator')
        );
    }
}
