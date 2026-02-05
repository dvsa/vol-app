<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Service;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService as LaminasAuthenticationService;
use Laminas\Authentication\Result;

class AuthenticationService extends LaminasAuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @param AdapterInterface|null $adapter
     * @return Result
     */
    #[\Override]
    public function authenticate(AdapterInterface $adapter = null): Result
    {
        return parent::authenticate($adapter);
    }
}
