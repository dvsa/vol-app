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
    /**
     * Dot-delimited paths into cpms_api.gateway that must hold a usable value before the gateway
     * can be built. `proxy` is deliberately absent: local development removes it.
     */
    private const REQUIRED_GATEWAY_KEYS = [
        'domain',
        'oauth2.client_id',
        'oauth2.client_secret',
        'oauth2.token_url',
        'oauth2.scope',
    ];

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
            $gatewayConfig = $config['cpms_api']['gateway'] ?? [];
            $this->assertGatewayConfigured($gatewayConfig);

            $config['cpms_api']['rest_client']['options']['domain'] = $gatewayConfig['domain'];
            $config['cpms_api']['rest_client']['options']['proxy'] = $gatewayConfig['proxy'] ?? null;

            $tokenProvider = $container->build(Provider::class, $gatewayConfig['oauth2']);
            $gatewayTokenProvider = new GatewayTokenProviderAdapter($tokenProvider);
        }

        $apiService = new CpmsApiService($config, $userId, $logger, $gatewayTokenProvider);

        return $apiService->createApiService();
    }

    /**
     * Fail early, and by name, when the gateway is switched on without credentials.
     *
     * An environment awaiting its Entra registration carries " " sentinels in Parameter Store,
     * which cannot hold an empty string. Those values arrive here present but unusable, so a
     * blank must be rejected exactly as a missing key is - otherwise the first symptom is an
     * opaque HTTP failure against a whitespace token URL.
     */
    private function assertGatewayConfigured(array $gatewayConfig): void
    {
        $unusable = [];

        foreach (self::REQUIRED_GATEWAY_KEYS as $path) {
            $value = $gatewayConfig;

            foreach (explode('.', $path) as $segment) {
                $value = is_array($value) ? ($value[$segment] ?? null) : null;
            }

            if (!is_string($value) || trim($value) === '') {
                $unusable[] = $path;
            }
        }

        if ($unusable !== []) {
            throw new \RuntimeException(sprintf(
                'CPMS hybrid gateway toggle is enabled but cpms_api.gateway config is missing or blank: %s',
                implode(', ', $unusable)
            ));
        }
    }
}
