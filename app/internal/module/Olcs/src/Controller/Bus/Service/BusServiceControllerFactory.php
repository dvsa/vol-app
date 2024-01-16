<?php

namespace Olcs\Controller\Bus\Service;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class BusServiceControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusServiceController
    {
        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $tableFactory = $container->get(TableFactory::class);
        assert($tableFactory instanceof TableFactory);

        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        return new BusServiceController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $tableFactory
        );
    }
}
