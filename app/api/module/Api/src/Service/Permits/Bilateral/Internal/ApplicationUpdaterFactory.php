<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicationUpdaterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationUpdater
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationUpdater
    {
        return new ApplicationUpdater(
            $container->get('PermitsBilateralInternalApplicationCountryUpdater')
        );
    }
}
