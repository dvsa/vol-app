<?php

declare(strict_types=1);

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Messages\LicenceCloseConversationController;

class LicenceCloseConversationControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string             $requestedName,
        ?array             $options = null
    ): LicenceCloseConversationController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $viewHelperManager = $container->get(HelperPluginManager::class);
        $flashMessengerHelperService = $container->get(FlashMessengerHelperService::class);

        return new LicenceCloseConversationController(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager,
            $flashMessengerHelperService,
        );
    }
}
