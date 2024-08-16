<?php

namespace Dvsa\Olcs\Api\Service\Permits\Bilateral\Internal;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IrhpPermitApplicationCreatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpPermitApplicationCreator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpPermitApplicationCreator
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new IrhpPermitApplicationCreator(
            $repoServiceManager->get('IrhpPermitStock'),
            $repoServiceManager->get('IrhpPermitApplication'),
            $container->get('PermitsBilateralInternalIrhpPermitApplicationFactory')
        );
    }
}
