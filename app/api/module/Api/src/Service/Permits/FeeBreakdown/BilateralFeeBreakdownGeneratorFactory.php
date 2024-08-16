<?php

namespace Dvsa\Olcs\Api\Service\Permits\FeeBreakdown;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BilateralFeeBreakdownGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return BilateralFeeBreakdownGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BilateralFeeBreakdownGenerator
    {
        return new BilateralFeeBreakdownGenerator(
            $container->get('RepositoryServiceManager')->get('FeeType')
        );
    }
}
