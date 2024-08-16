<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SuccessfulCandidatePermitsFacadeFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SuccessfulCandidatePermitsFacade
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SuccessfulCandidatePermitsFacade
    {
        return new SuccessfulCandidatePermitsFacade(
            $container->get('PermitsScoringSuccessfulCandidatePermitsGenerator'),
            $container->get('PermitsScoringSuccessfulCandidatePermitsWriter'),
            $container->get('PermitsScoringSuccessfulCandidatePermitsLogger')
        );
    }
}
