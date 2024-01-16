<?php

namespace Olcs\Controller\Application\Processing;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HistoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): HistoryController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        $tableBuilder = $container->get(TableFactory::class);
        assert($tableBuilder instanceof TableFactory);

        return new HistoryController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $tableBuilder
        );
    }
}
