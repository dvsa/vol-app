<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RangeBasedRestrictedCountriesProviderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return RangeBasedRestrictedCountriesProvider
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RangeBasedRestrictedCountriesProvider
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new RangeBasedRestrictedCountriesProvider(
            $repoServiceManager->get('IrhpPermitRange'),
            $container->get('PermitsCommonTypeBasedPermitTypeConfigProvider'),
            $repoServiceManager->get('Country')
        );
    }
}
