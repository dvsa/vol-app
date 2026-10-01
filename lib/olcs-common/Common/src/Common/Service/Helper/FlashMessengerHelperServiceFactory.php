<?php

namespace Common\Service\Helper;

use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FlashMessengerHelperServiceFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FlashMessengerHelperService
    {
        return new FlashMessengerHelperService(
            $container->get(LaminasSessionFlashMessenger::class),
        );
    }
}
