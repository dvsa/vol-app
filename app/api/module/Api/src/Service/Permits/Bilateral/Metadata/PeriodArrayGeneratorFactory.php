<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PeriodArrayGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PeriodArrayGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PeriodArrayGenerator
    {
        return new PeriodArrayGenerator(
            $container->get('RepositoryServiceManager')->get('IrhpPermitStock'),
            $container->get('PermitsBilateralMetadataPeriodGenerator'),
            $container->get('CommonCurrentDateTimeFactory')
        );
    }
}
