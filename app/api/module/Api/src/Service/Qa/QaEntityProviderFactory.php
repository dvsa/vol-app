<?php

namespace Dvsa\Olcs\Api\Service\Qa;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class QaEntityProviderFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return QaEntityProvider
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): QaEntityProvider
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new QaEntityProvider(
            $repoServiceManager->get('IrhpApplication'),
            $repoServiceManager->get('IrhpPermitApplication')
        );
    }
}
