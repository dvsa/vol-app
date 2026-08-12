<?php

namespace Dvsa\Olcs\Api\Service\Cpms;

use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\Olcs\Cpms\Service\ApiServiceFactory as CpmsApiService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Psr\Container\ContainerInterface;

class ApiServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');
        $authService = $container->get(AuthorizationService::class);
        $userId = $authService->getIdentity()->getUser()->getId();
        $logger = $container->get('Logger');

        $gatewayTokenProvider = null;

        /** @var ToggleService $toggleService */
        $toggleService = $container->get(ToggleService::class);

        if ($toggleService->isEnabled(FeatureToggle::CPMS_HYBRID_GATEWAY)) {
            $gatewayConfig = $config['cpms_api']['gateway'];
            $config['cpms_api']['rest_client']['options']['domain'] = $gatewayConfig['domain'];
            $config['cpms_api']['rest_client']['options']['proxy'] = $gatewayConfig['proxy'] ?? null;

            $tokenProvider = $container->build(Provider::class, $gatewayConfig['oauth2']);
            $gatewayTokenProvider = new GatewayTokenProviderAdapter($tokenProvider);
        }

        $apiService = new CpmsApiService($config, $userId, $logger, $gatewayTokenProvider);

        return $apiService->createApiService();
    }
}
