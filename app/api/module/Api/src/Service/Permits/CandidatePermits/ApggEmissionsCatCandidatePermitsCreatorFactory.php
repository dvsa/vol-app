<?php

namespace Dvsa\Olcs\Api\Service\Permits\CandidatePermits;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApggEmissionsCatCandidatePermitsCreatorFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApggEmissionsCatCandidatePermitsCreator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApggEmissionsCatCandidatePermitsCreator
    {
        return new ApggEmissionsCatCandidatePermitsCreator(
            $container->get('PermitsCandidatePermitsApggCandidatePermitFactory'),
            $container->get('RepositoryServiceManager')->get('IrhpCandidatePermit'),
            $container->get('PermitsAllocateEmissionsStandardCriteriaFactory')
        );
    }
}
