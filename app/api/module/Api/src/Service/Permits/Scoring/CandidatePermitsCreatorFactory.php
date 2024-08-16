<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CandidatePermitsCreatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CandidatePermitsCreator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CandidatePermitsCreator
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new CandidatePermitsCreator(
            $repoServiceManager->get('IrhpCandidatePermit'),
            $repoServiceManager->get('SystemParameter'),
            $container->get('PermitsScoringIrhpCandidatePermitFactory')
        );
    }
}
