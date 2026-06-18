<?php

declare(strict_types=1);

namespace Admin\Controller\S3;

use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class BrowserControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BrowserController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');
        $avScanner = $container->get(Scan::class);

        return new BrowserController($translationHelper, $formHelper, $flashMessenger, $navigation, $avScanner);
    }
}
