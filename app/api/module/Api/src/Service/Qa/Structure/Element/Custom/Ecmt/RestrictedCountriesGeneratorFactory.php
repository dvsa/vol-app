<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RestrictedCountriesGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RestrictedCountriesGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RestrictedCountriesGenerator
    {
        return new RestrictedCountriesGenerator(
            $container->get('QaEcmtRestrictedCountriesElementFactory'),
            $container->get('QaEcmtRestrictedCountryFactory'),
            $container->get('RepositoryServiceManager')->get('Country'),
            $container->get('QaGenericAnswerProvider'),
            $container->get('PermitsCommonStockBasedPermitTypeConfigProvider')
        );
    }
}
