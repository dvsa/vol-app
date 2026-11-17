<?php

namespace Common\Controller\Plugin;

use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class CommonFlashMessengerPluginFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LaminasSessionFlashMessenger
    {
        return $container->get(LaminasSessionFlashMessenger::class);
    }
}
