<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Client;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Dvsa\Authentication\Cognito\Client;
use GuzzleHttp\Client as HttpClient;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RuntimeException;

class CognitoClientFactory implements FactoryInterface
{
    public const CONFIG_NAMESPACE = 'auth';
    public const CONFIG_ADAPTERS = 'adapters';
    public const CONFIG_ADAPTER = 'cognito';
    public const CONFIG_REGION = 'region';
    public const CONFIG_CLIENT_ID = 'clientId';
    public const CONFIG_CLIENT_SECRET = 'clientSecret';
    public const CONFIG_POOL_ID = 'poolId';
    public const CONFIG_NBF_LEEWAY = 'nbfLeeway';
    public const CONFIG_HTTP = 'http';

    public const EXCEPTION_MESSAGE_NAMESPACE_MISSING = 'Cognito config missing from awsOptions';
    public const EXCEPTION_MESSAGE_OPTION_MISSING = 'Cognito config requires: clientId, clientSecret, poolId, region and http';

    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Client
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Client
    {
        $config = $container->get('config')[static::CONFIG_NAMESPACE][static::CONFIG_ADAPTERS][static::CONFIG_ADAPTER];

        $this->validateConfig($config);

        $awsClient = new CognitoIdentityProviderClient([
            'version' => '2016-04-18',
            'region' => $config[static::CONFIG_REGION],
            'http' => $config[static::CONFIG_HTTP]
        ]);

        // Account for clock skew - https://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDe
        Client::$leeway = $config[static::CONFIG_NBF_LEEWAY];

        $instance = new Client(
            $awsClient,
            $config[static::CONFIG_CLIENT_ID],
            $config[static::CONFIG_CLIENT_SECRET],
            $config[static::CONFIG_POOL_ID]
        );

        $httpClient = new HttpClient($config[static::CONFIG_HTTP]);
        $instance->setHttpClient($httpClient);

        return $instance;
    }

    /**
     * @return bool
     */
    protected function validateConfig(array $config): bool
    {
        if (empty($config)) {
            throw new RuntimeException(static::EXCEPTION_MESSAGE_NAMESPACE_MISSING);
        }

        if (
            !array_key_exists(static::CONFIG_CLIENT_ID, $config)
            || !array_key_exists(static::CONFIG_CLIENT_SECRET, $config)
            || !array_key_exists(static::CONFIG_POOL_ID, $config)
            || !array_key_exists(static::CONFIG_NBF_LEEWAY, $config)
            || !array_key_exists(static::CONFIG_REGION, $config)
            || !array_key_exists(static::CONFIG_HTTP, $config)
        ) {
            throw new RuntimeException(static::EXCEPTION_MESSAGE_OPTION_MISSING);
        }

        return true;
    }
}
