<?php

namespace Olcs\Controller\Cases\ConditionUndertaking;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\HelperPluginManager;

class ConditionUndertakingControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConditionUndertakingController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $viewHelperManager = $container->get('ViewHelperManager');
        assert($viewHelperManager instanceof HelperPluginManager);

        return new ConditionUndertakingController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $viewHelperManager
        );
    }
}
