<?php

namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\LicenceTransportManagerAdapter as CommonAdapter;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Lva\VariationLvaService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;

/**
 * External Licence Transport Manager Adater
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class LicenceTransportManagerAdapter extends CommonAdapter
{
    /** @var VariationLvaService */
    private $lvaVariationSrv;

    /**
     * LicenceTransportManagerAdapter constructor.
     *
     * @param TransferAnnotationBuilder $transferAnnotationBuilder annotation builder
     * @param CachingQueryService       $querySrv                  caching query service
     * @param CommandService            $commandSrv                command service
     * @param VariationLvaService       $lvaVariationSrv           lva variation service
     *
     * @return void
     */
    public function __construct(
        TransferAnnotationBuilder $transferAnnotationBuilder,
        CachingQueryService $querySrv,
        CommandService $commandSrv,
        VariationLvaService $lvaVariationSrv
    ) {
        parent::__construct($transferAnnotationBuilder, $querySrv, $commandSrv);

        $this->lvaVariationSrv = $lvaVariationSrv;
    }

    /**
     * Add messages
     *
     * @param int $licenceId licence id
     *
     * @return void
     */
    public function addMessages($licenceId)
    {
        // add message saying to create a variation
        $this->lvaVariationSrv->addVariationMessage($licenceId, 'transport_managers', 'variation-message-add-tm');
    }
}
