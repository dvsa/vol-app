<?php

namespace Dvsa\Olcs\Api\Service\Permits;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GrantabilityCheckerFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GrantabilityChecker
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GrantabilityChecker
    {
        return new GrantabilityChecker(
            $container->get('PermitsAvailabilityEmissionsCategoriesGrantabilityChecker'),
            $container->get('PermitsAvailabilityCandidatePermitsGrantabilityChecker')
        );
    }
}
