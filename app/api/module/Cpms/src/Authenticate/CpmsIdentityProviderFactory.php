<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Authenticate;

class CpmsIdentityProviderFactory
{
    public function __construct(private readonly string $clientId, private readonly string $clientSecret, private readonly string $userId)
    {
    }

    public function createCpmsIdentityProvider(): CpmsIdentityProvider
    {
        $identityProvider = new CpmsIdentityProvider($this->userId, $this->clientId, $this->clientSecret);
        return $identityProvider;
    }
}
