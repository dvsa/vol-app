<?php

namespace Dvsa\Olcs\Api\Service\Cpms;

use Dvsa\Olcs\Cpms\Service\ApiServiceFactory as CpmsApiService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Psr\Container\ContainerInterface;

class ApiServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $authService = $container->get(AuthorizationService::class);
        $userId = $authService->getIdentity()->getUser()->getId();
        $apiService = new CpmsApiService($config, $userId);
        return $apiService->createApiService();
    }
}
