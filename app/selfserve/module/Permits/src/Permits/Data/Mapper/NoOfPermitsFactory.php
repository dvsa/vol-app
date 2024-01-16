<?php

namespace Permits\Data\Mapper;

use Interop\Container\ContainerInterface;
use Common\Data\Mapper\Permits\NoOfPermits as CommonNoOfPermits;
use Laminas\ServiceManager\Factory\FactoryInterface;

class NoOfPermitsFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return NoOfPermits
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermits
    {
        $mapperManager = $container->get(MapperManager::class);
        return new NoOfPermits(
            $mapperManager->get(CommonNoOfPermits::class)
        );
    }
}
