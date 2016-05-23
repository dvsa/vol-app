<?php

namespace Olcs\Controller\Lva\Factory\Adapter;

use Olcs\Controller\Lva\Adapters\LicenceTransportManagerAdapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creation Licence Transport Manager Adapter
 * 
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class LicenceTransportManagerAdapterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder $transferAnnotationBuilder */
        $transferAnnotationBuilder = $sl->get('TransferAnnotationBuilder');
        /** @var \Common\Service\Cqrs\Query\CachingQueryService $querySrv */
        $querySrv = $sl->get('QueryService');
        /** @var \Common\Service\Cqrs\Command\CommandService $commandSrv */
        $commandSrv = $sl->get('CommandService');
        /** @var \Common\Service\Lva\VariationLvaService $variationSrv */
        $variationSrv = $sl->get('Lva\Variation');

        return new LicenceTransportManagerAdapter(
            $transferAnnotationBuilder,
            $querySrv,
            $commandSrv,
            $variationSrv
        );
    }
}
