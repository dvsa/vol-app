<?php

namespace Olcs\Controller\TransportManager\Processing;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TransportManagerProcessingNoteControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerProcessingNoteController
    {
        $translationHelperService = $container->get(TranslationHelperService::class);
        assert($translationHelperService instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessengerHelperService = $container->get(FlashMessengerHelperService::class);
        assert($flashMessengerHelperService instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        return new TransportManagerProcessingNoteController(
            $translationHelperService,
            $formHelperService,
            $flashMessengerHelperService,
            $navigation
        );
    }
}
