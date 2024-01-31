<?php

namespace Admin\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;

class CompaniesHouseAlertControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CompaniesHouseAlertController
    {
        $translationHelperService = $container->get(TranslationHelperService::class);
        assert($translationHelperService instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessengerHelperService = $container->get(FlashMessengerHelperService::class);
        assert($flashMessengerHelperService instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $viewHelperManager = $container->get('ViewHelperManager');
        assert($viewHelperManager instanceof HelperPluginManager);

        return new CompaniesHouseAlertController(
            $translationHelperService,
            $formHelperService,
            $flashMessengerHelperService,
            $navigation,
            $viewHelperManager
        );
    }
}
