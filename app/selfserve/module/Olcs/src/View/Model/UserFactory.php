<?php

declare(strict_types=1);

namespace Olcs\View\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class UserFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): User
    {
        return new User(
            $container->get('Helper\Url'),
            $container->get('Table')
        );
    }

    /**
     * @deprecated can be removed following Laminas 3.0 upgrade
     */
    public function createService(ServiceLocatorInterface $serviceLocator): User
    {
        return $this($serviceLocator, User::class);
    }
}
