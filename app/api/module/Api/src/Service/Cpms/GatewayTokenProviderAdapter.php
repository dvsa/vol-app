<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Cpms;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Dvsa\Olcs\Cpms\Authenticate\GatewayTokenProviderInterface;

/**
 * Bridges the generic Entra access-token provider onto the Cpms module's gateway token interface.
 */
class GatewayTokenProviderAdapter implements GatewayTokenProviderInterface
{
    public function __construct(private readonly Provider $provider)
    {
    }

    #[\Override]
    public function getToken(): string
    {
        return $this->provider->getToken();
    }
}
