<?php

declare(strict_types=1);

namespace Olcs\View\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UserFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): User
    {
        return new User(
            $container->get('Helper\Url'),
            $container->get('Table')
        );
    }
}
