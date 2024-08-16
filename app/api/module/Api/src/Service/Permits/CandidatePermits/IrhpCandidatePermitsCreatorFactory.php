<?php

namespace Dvsa\Olcs\Api\Service\Permits\CandidatePermits;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IrhpCandidatePermitsCreatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpCandidatePermitsCreator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpCandidatePermitsCreator
    {
        return new IrhpCandidatePermitsCreator(
            $container->get('PermitsScoringCandidatePermitsCreator'),
            $container->get('PermitsCandidatePermitsApggCandidatePermitsCreator')
        );
    }
}
