<?php

namespace Common\View\Factory\Helper;

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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FlashMessenger
    {
        $flashMessengerHelperService = $container->get('Helper\FlashMessenger');

        $flashMessenger = new FlashMessenger($flashMessengerHelperService);

        $flashMessenger->setPluginFlashMessenger($container->get('ControllerPluginManager')->get('FlashMessenger'));

        return $flashMessenger;
    }
}
