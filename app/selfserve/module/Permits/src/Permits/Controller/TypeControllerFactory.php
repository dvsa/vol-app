<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Permits\Data\Mapper\MapperManager;

class TypeControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TypeController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TypeController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get(TableFactory::class);
        $mapperManager = $container->get(MapperManager::class);
        return new TypeController($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }
}
