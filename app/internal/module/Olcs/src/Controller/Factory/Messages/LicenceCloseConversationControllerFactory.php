<?php

declare(strict_types=1);

namespace Olcs\Controller\Factory\Messages;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Laminas\Navigation\Navigation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Controller\Messages\LicenceCloseConversationController;
use Olcs\Controller\Messages\LicenceNewConversationController;
use Olcs\Controller\TaskController;
use Olcs\Service\Data\SubCategory;
use Olcs\Service\Data\UserListInternalExcludingLimitedReadOnlyUsers;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LicenceCloseConversationControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string             $requestedName,
        ?array             $options = null
    ): LicenceCloseConversationController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

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

    public function createService(ServiceLocatorInterface $serviceLocator): LicenceCloseConversationController
    {
        return $this->__invoke($serviceLocator, LicenceCloseConversationController::class);
    }
}
