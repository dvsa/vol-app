<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NoOfPermitsGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return NoOfPermitsGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermitsGenerator
    {
        return new NoOfPermitsGenerator(
            $container->get('RepositoryServiceManager')->get('FeeType'),
            $container->get('QaEcmtNoOfPermitsElementFactory'),
            $container->get('QaEcmtEmissionsCategoryConditionalAdder'),
            $container->get('PermitsAvailabilityStockAvailabilityCounter'),
            $container->get('PermitsAvailabilityStockLicenceMaxPermittedCounter')
        );
    }
}
