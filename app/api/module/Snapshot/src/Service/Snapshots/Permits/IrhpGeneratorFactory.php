<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Permits;

use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IrhpGeneratorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpGenerator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpGenerator
    {
        return new IrhpGenerator(
            $container->get(AbstractGeneratorServices::class),
            $container->get('PermitsAnswersSummaryGenerator')
        );
    }
}
