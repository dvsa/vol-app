<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LetterAppendixControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LetterAppendixController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');
        $scannerAntiVirusService = $container->get(Scan::class);

        return new LetterAppendixController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $scannerAntiVirusService
        );
    }
}
