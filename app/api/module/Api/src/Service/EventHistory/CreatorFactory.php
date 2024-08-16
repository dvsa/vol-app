<?php

namespace Dvsa\Olcs\Api\Service\EventHistory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Psr\Container\ContainerInterface;

class CreatorFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Creator
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Creator
    {
        $repoServiceManager = $container->get('RepositoryServiceManager');
        return new Creator(
            $container->get(AuthorizationService::class),
            $repoServiceManager->get('EventHistory'),
            $repoServiceManager->get('EventHistoryType')
        );
    }
}
