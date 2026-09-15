<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\AccessToken;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Olcs\Logging\Log\Logger;

class Provider
{
    public const MSG_ERROR = 'Failed to retrieve access token for %s: %s';

    private const EXPIRY_MARGIN_SECONDS = 60;

    private ?AccessTokenInterface $accessToken = null;

    public function __construct(
        private readonly AbstractProvider $provider,
        private readonly string $scope,
        private readonly string $serviceName = '',
    ) {
    }

    public function getToken(): string
    {
        if (!$this->accessToken instanceof AccessTokenInterface || $this->isExpiring($this->accessToken)) {
            $this->accessToken = $this->fetchAccessToken();
        }

        return $this->accessToken->getToken();
    }

    private function isExpiring(AccessTokenInterface $accessToken): bool
    {
        $expires = $accessToken->getExpires();

        if ($expires === null) {
            return false;
        }

        return ($expires - self::EXPIRY_MARGIN_SECONDS) <= time();
    }

    private function fetchAccessToken(): AccessTokenInterface
    {
        try {
            return $this->provider->getAccessToken('client_credentials', ['scope' => $this->scope]);
        } catch (IdentityProviderException $e) {
            Logger::err(sprintf(self::MSG_ERROR, $this->serviceName, $e->getMessage()));
            throw $e;
        }
    }
}
