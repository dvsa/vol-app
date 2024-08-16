<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\AccessToken;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

class Provider
{
    public function __construct(private readonly AccessToken|AccessTokenInterface $accessToken)
    {
    }

    public function getToken(): string
    {
        return $this->accessToken->getToken();
    }
}
