<?php

namespace Common\View\Factory\Helper;

use Common\Service\FlashMessenger\LaminasSessionFlashMessenger;
use Common\View\Helper\FlashMessenger;
use Common\Service\Helper\FlashMessengerHelperService;
use Laminas\I18n\Translator\Translator;
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
        $flashMessengerHelperService = $container->get('Helper\FlashMessenger');
        $flashMessengerPlugin = $container->get(LaminasSessionFlashMessenger::class);
        $translator = $container->get(Translator::class);
        return new FlashMessenger($flashMessengerHelperService, $flashMessengerPlugin, $translator);
    }
}
