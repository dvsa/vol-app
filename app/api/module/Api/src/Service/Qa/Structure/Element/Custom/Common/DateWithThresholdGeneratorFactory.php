<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class DateWithThresholdGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DateWithThresholdGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DateWithThresholdGenerator
    {
        return new DateWithThresholdGenerator(
            $container->get('QaCommonDateWithThresholdElementFactory'),
            $container->get('CommonCurrentDateTimeFactory'),
            $container->get('QaCommonDateIntervalFactory'),
            $container->get('QaDateElementGenerator')
        );
    }
}
