<?php

namespace Admin\Controller;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Service\Data\SubCategory;
use Psr\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Data\UserListInternal;

class TaskAllocationRulesControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TaskAllocationRulesController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $tableFactory = $container->get(TableFactory::class);
        assert($tableFactory instanceof TableFactory);

        $userListInternal = $container->get(PluginManager::class)->get(UserListInternal::class);
        assert($userListInternal instanceof UserListInternal);

        $subCategory = $container->get(PluginManager::class)->get(SubCategory::class);

        return new TaskAllocationRulesController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $tableFactory,
            $userListInternal,
            $subCategory
        );
    }
}
