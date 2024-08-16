<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\EcmtRemoval;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PermitStartDateGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PermitStartDateGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PermitStartDateGenerator
    {
        return new PermitStartDateGenerator(
            $container->get('QaCommonDateWithThresholdElementGenerator')
        );
    }
}
