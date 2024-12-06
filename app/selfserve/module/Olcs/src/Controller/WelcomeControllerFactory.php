<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Permits\Data\Mapper\MapperManager;

class WelcomeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): WelcomeController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get(TableFactory::class);
        $mapperManager = $container->get(MapperManager::class);
        $urlHelper = $container->get(UrlHelperService::class);

        return new WelcomeController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $urlHelper);
    }
}
