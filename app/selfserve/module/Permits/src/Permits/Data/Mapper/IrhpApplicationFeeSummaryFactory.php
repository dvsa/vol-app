<?php

namespace Permits\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class IrhpApplicationFeeSummaryFactory implements FactoryInterface
{
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
