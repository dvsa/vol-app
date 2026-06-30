<?php

declare(strict_types=1);

namespace Common\Auth\Service;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationServiceInterface as LaminasAuthenticationServiceInterface;
use Laminas\Authentication\Result;

interface AuthenticationServiceInterface extends LaminasAuthenticationServiceInterface
{
    /**
     * Authenticates and provides an authentication result
     */
    #[\Override]
    public function authenticate(AdapterInterface $adapter = null): Result;
}
