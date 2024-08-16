<?php

namespace Dvsa\Olcs\Api\Service\Permits\CandidatePermits;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApggCandidatePermitsCreatorFactory implements FactoryInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApggCandidatePermitsCreator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApggCandidatePermitsCreator
    {
        return new ApggCandidatePermitsCreator(
            $container->get('PermitsCandidatePermitsApggEmissionsCatCandidatePermitsCreator')
        );
    }
}
