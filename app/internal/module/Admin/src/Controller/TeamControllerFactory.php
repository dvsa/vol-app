<?php

namespace Admin\Controller;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;
use Olcs\Service\Data\SubCategory;
use Olcs\Service\Data\UserWithName;

class TeamControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TeamController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $viewHelperManager = $container->get('ViewHelperManager');
        assert($viewHelperManager instanceof HelperPluginManager);

        $tableFactory = $container->get(TableFactory::class);
        assert($tableFactory instanceof TableFactory);

        $subCategory = $container->get(PluginManager::class)->get(SubCategory::class);
        assert($subCategory instanceof SubCategory);

        $userWithNameService = $container->get(UserWithName::class);
        assert($userWithNameService instanceof UserWithName);

        return new TeamController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $viewHelperManager,
            $tableFactory,
            $subCategory,
            $userWithNameService
        );
    }
}
