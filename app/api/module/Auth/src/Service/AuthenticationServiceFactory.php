<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Auth\Service;

use Psr\Container\ContainerInterface;
use Laminas\Authentication\Storage\NonPersistent;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Class AuthenticationServiceFactory
 * @see \AuthenticationServiceFactoryTest
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthenticationService
    {
        return new AuthenticationService(new NonPersistent());
    }
}
