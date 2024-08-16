<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SuccessfulCandidatePermitsWriterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SuccessfulCandidatePermitsWriter
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SuccessfulCandidatePermitsWriter
    {
        return new SuccessfulCandidatePermitsWriter(
            $container->get('RepositoryServiceManager')->get('IrhpCandidatePermit')
        );
    }
}
