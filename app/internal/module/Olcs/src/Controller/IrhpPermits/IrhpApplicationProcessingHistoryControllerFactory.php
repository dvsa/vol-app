<?php

namespace Olcs\Controller\IrhpPermits;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IrhpApplicationProcessingHistoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpApplicationProcessingHistoryController
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

        return new IrhpApplicationProcessingHistoryController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $tableFactory
        );
    }
}
