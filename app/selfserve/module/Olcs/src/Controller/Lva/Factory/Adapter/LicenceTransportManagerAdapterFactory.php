<?php

namespace Olcs\Controller\Lva\Factory\Adapter;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Lva\VariationLvaService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Interop\Container\ContainerInterface;
use Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creation Licence Transport Manager Adapter
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class LicenceTransportManagerAdapterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator) : LicenceTransportManagerAdapter
    {
        return $this->__invoke($serviceLocator, LicenceTransportManagerAdapter::class);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : LicenceTransportManagerAdapter
    {
        $transferAnnotationBuilder = $container->get(AnnotationBuilder::class);
        assert($transferAnnotationBuilder instanceof AnnotationBuilder);

        $queryService = $container->get(CachingQueryService::class);
        assert($queryService instanceof CachingQueryService);

        $commandService = $container->get(CommandService::class);
        assert($commandService instanceof CommandService);

        $variationService = $container->get(VariationLvaService::class);
        assert($variationService instanceof VariationLvaService);

        return new LicenceTransportManagerAdapter(
            $transferAnnotationBuilder,
            $queryService,
            $commandService,
            $variationService,
            $container
        );
    }
}
