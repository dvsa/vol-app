<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PiControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PiController
    {
        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $scriptService = $container->get(ScriptFactory::class);
        assert($scriptService instanceof ScriptFactory);

        $tableFactory = $container->get(TableFactory::class);
        assert($tableFactory instanceof TableFactory);

        return new PiController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $scriptService,
            $tableFactory
        );
    }
}
