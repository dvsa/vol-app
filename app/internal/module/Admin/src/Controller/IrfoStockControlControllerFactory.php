<?php

namespace Admin\Controller;

use Common\Service\Data\PluginManager;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Data\IrfoCountry;
use Psr\Container\ContainerInterface;

class IrfoStockControlControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrfoStockControlController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        $dateHelperService = $container->get(DateHelperService::class);
        assert($dateHelperService instanceof DateHelperService);

        $irfoCountryDataService = $container->get(PluginManager::class)->get(IrfoCountry::class);
        assert($irfoCountryDataService instanceof IrfoCountry);

        return new IrfoStockControlController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $dateHelperService,
            $irfoCountryDataService
        );
    }
}
