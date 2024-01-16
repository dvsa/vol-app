<?php

namespace Admin\Controller\DataRetention;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ExportControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ExportController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        $tableFactory = $container->get(TableFactory::class);
        assert($tableFactory instanceof TableFactory);

        $responseHelperService = $container->get(ResponseHelperService::class);
        assert($responseHelperService instanceof ResponseHelperService);

        return new ExportController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $tableFactory,
            $responseHelperService
        );
    }
}
