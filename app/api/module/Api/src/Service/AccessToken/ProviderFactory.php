<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\AccessToken;

use Laminas\ServiceManager\Factory\FactoryInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;

class ProviderFactory implements FactoryInterface
{
    public const MSG_ERROR = 'Failed to retrieve access token for %s: %s';

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Provider
    {
        $providerConfig = [
            'clientId' => $options['client_id'],
            'clientSecret' => $options['client_secret'],
            'urlAccessToken' => $options['token_url'],
            'urlAuthorize' => $options['token_url'],
            'urlResourceOwnerDetails' => $options['token_url'],
        ];

        if (isset($options['proxy'])) {
            $providerConfig['proxy'] = $options['proxy'];
        }

        $provider = new GenericProvider($providerConfig);

        try {
            $accessToken = $provider->getAccessToken('client_credentials', ['scope' => $options['scope']]);
            return new Provider($accessToken);
        } catch (IdentityProviderException $e) {
            $message = sprintf(self::MSG_ERROR, $options['service_name'], $e->getMessage());
            Logger::err($message);
            throw $e;
        }
    }
}
