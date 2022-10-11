<?php

namespace Olcs\Controller\Lva\Factory\Adapter;

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

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return LicenceTransportManagerAdapter
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : LicenceTransportManagerAdapter
    {
        /** @var \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder $transferAnnotationBuilder */
        $transferAnnotationBuilder = $container->get('TransferAnnotationBuilder');
        /** @var \Common\Service\Cqrs\Query\CachingQueryService $querySrv */
        $querySrv = $container->get('QueryService');
        /** @var \Common\Service\Cqrs\Command\CommandService $commandSrv */
        $commandSrv = $container->get('CommandService');
        /** @var \Common\Service\Lva\VariationLvaService $variationSrv */
        $variationSrv = $container->get('Lva\Variation');
        return new LicenceTransportManagerAdapter(
            $transferAnnotationBuilder,
            $querySrv,
            $commandSrv,
            $variationSrv
        );
    }
}
