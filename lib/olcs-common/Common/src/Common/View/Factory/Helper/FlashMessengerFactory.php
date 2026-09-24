<?php

namespace Common\View\Factory\Helper;

use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Common\View\Helper\FlashMessenger;
use Common\Service\Helper\FlashMessengerHelperService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @see Common\View\Helper\FlashMessenger
 */
class FlashMessengerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): FlashMessenger
    {
        $flashMessengerHelperService = $container->get(FlashMessengerHelperService::class);
        $flashMessengerPlugin = $container->get(LaminasSessionFlashMessenger::class);
        $translator = $container->get('translator');
        return new FlashMessenger($flashMessengerHelperService, $flashMessengerPlugin, $translator);
    }
}
