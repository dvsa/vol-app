<?php

namespace Admin\Controller;

use Common\Service\AntiVirus\Scan;
use Common\Service\Data\PluginManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Service\Data\SubCategory;
use Psr\Container\ContainerInterface;

class DocumentTemplateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DocumentTemplateController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelperService = $container->get(FormHelperService::class);
        assert($formHelperService instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $scannerAntiVirusService = $container->get(Scan::class);
        assert($scannerAntiVirusService instanceof Scan);

        $subCategoryDataService = $container->get(PluginManager::class)->get(SubCategory::class);
        assert($subCategoryDataService instanceof SubCategory);

        return new DocumentTemplateController(
            $translationHelper,
            $formHelperService,
            $flashMessenger,
            $navigation,
            $scannerAntiVirusService,
            $subCategoryDataService
        );
    }
}
