<?php

declare(strict_types=1);

namespace Common\Auth\Service;

use Psr\Container\ContainerInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AuthenticationService
    {
        $instance = new AuthenticationService();
        $instance->setStorage($container->get(Session::class));

        return $instance;
    }
}
