<?php

namespace Dvsa\Olcs\Api\Domain\Util;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class TimeProcessorBuilderFactory
 */
class TimeProcessorBuilderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TimeProcessorBuilder
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TimeProcessorBuilder
    {
        return new TimeProcessorBuilder($container->get('RepositoryServiceManager')->get('PublicHoliday'));
    }
}
