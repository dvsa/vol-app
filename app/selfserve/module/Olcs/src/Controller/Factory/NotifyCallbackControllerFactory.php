<?php

namespace Olcs\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\NotifyCallbackController;
use Psr\Container\ContainerInterface;

class NotifyCallbackControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): NotifyCallbackController
    {
        $config = $container->get('config');

        return new NotifyCallbackController(
            (string) ($config['notify']['callback_token'] ?? ''),
            $container->get('CommandSender')
        );
    }
}
