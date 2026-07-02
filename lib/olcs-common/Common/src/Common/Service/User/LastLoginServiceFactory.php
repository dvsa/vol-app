<?php

namespace Common\Service\User;

use Common\Service\Cqrs\Command\CommandSender;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class LastLoginServiceFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LastLoginService
    {
        /** @var CommandSender $commandSender */
        $commandSender = $container->get('CommandSender');

        return new LastLoginService($commandSender);
    }
}
