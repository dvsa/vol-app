<?php

namespace Dvsa\Olcs\DvsaAddressService\Client;

use GuzzleHttp\Client;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Dvsa\Olcs\DvsaAddressService\Exception\IdentityProviderException as AddressServiceIdentityProviderException;
use Psr\Container\NotFoundExceptionInterface;

class DvsaAddressServiceClientFactory implements FactoryInterface
{
    protected const CONFIG_NAMESPACE = 'dvsa_address_service';
    protected const CONFIG_CLIENT_ROOT = 'client';
    protected const CONFIG_CLIENT_KEY_BASE_URL = 'base_uri';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws AddressServiceIdentityProviderException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DvsaAddressServiceClient
    {
        $config = $this->getDvsaAddressServiceConfig($container);
        $client = $this->getHttpClient($config);

        return new DvsaAddressServiceClient($client);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDvsaAddressServiceConfig(ContainerInterface $container): array
    {
        $config = $container->get('config');

        if (!isset($config[static::CONFIG_NAMESPACE])) {
            throw new \RuntimeException('Dvsa Address Service config not set');
        }
        return $config[static::CONFIG_NAMESPACE];
    }

    /**
     * @throws AddressServiceIdentityProviderException
     */
    private function getHttpClient(array $config): Client
    {
        $client_config = $config[static::CONFIG_CLIENT_ROOT];

        $default_header_config = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'DvsaAddressService/VOL-API',
            ],
        ];

        // If the client headers config does not have a 'Authorization' key, then we should add it
        if (!array_key_exists('Authorization', $client_config['headers'])) {
            $client_config['headers']['Authorization'] = 'Bearer ' . $this->getAppRegistrationServiceToken($config);
        }

        return new Client(array_merge($default_header_config, $client_config));
    }

    /**
     * @throws AddressServiceIdentityProviderException
     */
    protected function getAppRegistrationServiceToken(array $config): string
    {
        $providerConfig = [
            'clientId' => $config['oauth2']['client_id'],
            'clientSecret' => $config['oauth2']['client_secret'],
            'urlAccessToken' => $config['oauth2']['token_url'],
            'urlAuthorize' => $config['oauth2']['token_url'],
            'urlResourceOwnerDetails' => $config['oauth2']['token_url'],
        ];

        if (!empty($config['oauth2']['proxy'] ?? null)) {
            $providerConfig['proxy'] = $config['oauth2']['proxy'];
        }

        $provider = new \League\OAuth2\Client\Provider\GenericProvider($providerConfig);

        try {
            return $provider->getAccessToken('client_credentials', ['scope' => $config['oauth2']['scope']])->getToken();
        } catch (IdentityProviderException $e) {
            throw new AddressServiceIdentityProviderException('Failed to get access token for DVSA Address Service: ' . $e->getMessage(), previous: $e);
        }
    }
}
