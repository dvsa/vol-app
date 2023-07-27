<?php

namespace Olcs\Controller\Lva\Factory\Adapter;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Adapters\VariationTransportManagerAdapter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Factory for creation Variation Transport Manager Adapter
 * 
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class VariationTransportManagerAdapterFactory implements FactoryInterface
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     *
     * @deprecated Laminas 2 compatibility. To be removed after Laminas 3 upgrade.
     */
    public function createService(ServiceLocatorInterface $serviceLocator): VariationTransportManagerAdapter
    {
        $container = method_exists($serviceLocator, 'getServiceLocator') ? $serviceLocator->getServiceLocator() : $serviceLocator;

        return $this->__invoke($container, VariationTransportManagerAdapter::class);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationTransportManagerAdapter
    {
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        assert($transferAnnotationBuilder instanceof AnnotationBuilder);

        $queryService = $container->get(CachingQueryService::class);
        assert($queryService instanceof CachingQueryService);

        $commandService = $container->get(CommandService::class);
        assert($commandService instanceof CommandService);

        return new VariationTransportManagerAdapter($transferAnnotationBuilder, $queryService, $commandService, $container);
    }
}
