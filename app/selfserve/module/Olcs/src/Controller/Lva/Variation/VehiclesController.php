<?php

/**
 * External Variation Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractGenericVehiclesGoodsController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * External Variation Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesGoodsController
{
    use VariationControllerTrait,
        Traits\ApplicationGenericVehiclesControllerTrait,
        Traits\ApplicationGoodsVehiclesControllerTrait {
            Traits\ApplicationGoodsVehiclesControllerTrait::alterTable as traitAlterTable;
        }

    protected $lva = 'variation';
    protected $location = 'external';

    /**
     * This method handles calling both the trait's alterTable method, and it's parents
     */
    protected function alterTable($table)
    {
        return parent::alterTable($this->traitAlterTable($table));
    }
}
