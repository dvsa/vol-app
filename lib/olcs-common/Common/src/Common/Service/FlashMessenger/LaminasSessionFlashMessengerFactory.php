<?php

namespace Common\Service\FlashMessenger;

use Laminas\Session\Container;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LaminasSessionFlashMessengerFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LaminasSessionFlashMessenger
    {
        return new LaminasSessionFlashMessenger(new Container('FlashMessenger', Container::getDefaultManager()));
    }
}
