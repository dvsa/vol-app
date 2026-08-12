<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cpms\Authenticate;

/**
 * Supplies the bearer token that authenticates every request to the CPMS Hybrid Gateway.
 */
interface GatewayTokenProviderInterface
{
    public function getToken(): string;
}
