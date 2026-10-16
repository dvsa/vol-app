<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\AccessToken;

use Laminas\ServiceManager\Factory\FactoryInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Container\ContainerInterface;

class ProviderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Provider
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

        return new Provider(
            new GenericProvider($providerConfig),
            $options['scope'],
            $options['service_name'] ?? ''
        );
    }
}
