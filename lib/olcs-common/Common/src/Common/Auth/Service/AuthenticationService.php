<?php

declare(strict_types=1);

namespace Common\Auth\Service;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService as LaminasAuthenticationService;
use Laminas\Authentication\Result;

class AuthenticationService extends LaminasAuthenticationService implements AuthenticationServiceInterface
{
    #[\Override]
    public function authenticate(AdapterInterface $adapter = null): Result
    {
        return parent::authenticate($adapter);
    }
}
