<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PermitUsageUpdaterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return PermitUsageUpdater
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PermitUsageUpdater
    {
        return new PermitUsageUpdater(
            $container->get('PermitsBilateralCommonModifiedAnswerUpdater')
        );
    }
}
