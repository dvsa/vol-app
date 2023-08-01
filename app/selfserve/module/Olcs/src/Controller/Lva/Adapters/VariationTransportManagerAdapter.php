<?php

namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\VariationTransportManagerAdapter as CommonAdapter;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Interop\Container\ContainerInterface;

/**
 * Variation Transport Manager Adapter
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class VariationTransportManagerAdapter extends CommonAdapter
{
    protected $tableSortMethod = self::SORT_LAST_FIRST_NAME_NEW_AT_END;

    /**
     * @param TransferAnnotationBuilder $transferAnnotationBuilder
     * @param CachingQueryService $querySrv
     * @param CommandService $commandSrv
     * @param ContainerInterface $container
     */
    public function __construct(
        TransferAnnotationBuilder $transferAnnotationBuilder,
        CachingQueryService $querySrv,
        CommandService $commandSrv,
        ContainerInterface $container
    ) {
        parent::__construct($transferAnnotationBuilder, $querySrv, $commandSrv, $container);
    }
}
