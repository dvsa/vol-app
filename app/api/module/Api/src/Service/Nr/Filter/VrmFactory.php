<?php

namespace Dvsa\Olcs\Api\Service\Nr\Filter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Transfer\Filter\Vrm as TransferVrmFilter;
use Psr\Container\ContainerInterface;

/**
 * Class VrmFactory
 * @package Dvsa\Olcs\Api\Service\Nr\Filter
 */
class VrmFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Vrm
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Vrm
    {
        $service = new Vrm();
        $service->setVrmFilter($container->get(TransferVrmFilter::class));
        return $service;
    }
}
