<?php

namespace Common\Controller\Plugin;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HandleCommandFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): HandleCommand
    {
        return new HandleCommand($container->get('CommandSender'), $container->get('Helper\FlashMessenger'));
    }
}
