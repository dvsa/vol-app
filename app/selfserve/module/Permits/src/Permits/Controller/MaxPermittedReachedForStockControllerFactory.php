<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class MaxPermittedReachedForStockControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return MaxPermittedReachedForStockController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MaxPermittedReachedForStockController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get(TableFactory::class);
        $mapperManager = $container->get(MapperManager::class);
        return new MaxPermittedReachedForStockController($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return MaxPermittedReachedForStockController
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): MaxPermittedReachedForStockController
    {
        return $this->__invoke($serviceLocator, MaxPermittedReachedForStockController::class);
    }
}
