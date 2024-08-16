<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ApplicationCountryUpdaterFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ApplicationCountryUpdater
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApplicationCountryUpdater
    {
        return new ApplicationCountryUpdater(
            $container->get('PermitsBilateralInternalIrhpPermitApplicationCreator'),
            $container->get('PermitsBilateralInternalExistingIrhpPermitApplicationHandler')
        );
    }
}
