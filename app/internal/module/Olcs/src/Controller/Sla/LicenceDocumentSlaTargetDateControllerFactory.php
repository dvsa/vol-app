<?php

namespace Olcs\Controller\Sla;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LicenceDocumentSlaTargetDateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceDocumentSlaTargetDateController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        return new LicenceDocumentSlaTargetDateController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation
        );
    }
}
