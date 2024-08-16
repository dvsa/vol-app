<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Metadata;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PeriodGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PeriodGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PeriodGenerator
    {
        $periodGenerator = new PeriodGenerator(
            $container->get('RepositoryServiceManager')->get('IrhpPermitStock')
        );
        $periodGenerator->registerFieldsGenerator(
            Behaviour::STANDARD,
            $container->get('PermitsBilateralMetadataStandardFieldsGenerator')
        );
        $periodGenerator->registerFieldsGenerator(
            Behaviour::MOROCCO,
            $container->get('PermitsBilateralMetadataMoroccoFieldsGenerator')
        );
        return $periodGenerator;
    }
}
