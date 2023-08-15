<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class IrhpApplicationFeeSummaryFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IrhpApplicationFeeSummary
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IrhpApplicationFeeSummary
    {
        return $this->__invoke($serviceLocator, IrhpApplicationFeeSummary::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpApplicationFeeSummary
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpApplicationFeeSummary
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $viewHelperManager = $container->get('ViewHelperManager');
        $mapperManager = $container->get(MapperManager::class);
        return new IrhpApplicationFeeSummary(
            $container->get(TranslationHelperService::class),
            $mapperManager->get(EcmtNoOfPermits::class),
            $viewHelperManager->get('status'),
            $viewHelperManager->get('currencyFormatter'),
            $container->get(UrlHelperService::class)
        );
    }
}
