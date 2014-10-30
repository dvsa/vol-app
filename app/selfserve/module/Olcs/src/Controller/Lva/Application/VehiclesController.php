<?php

/**
 * External Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractGenericVehiclesGoodsController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * External Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesGoodsController
{
    use ApplicationControllerTrait,
        Traits\ApplicationGenericVehiclesControllerTrait,
        Traits\ApplicationGoodsVehiclesControllerTrait {
            Traits\ApplicationGoodsVehiclesControllerTrait::alterTable as traitAlterTable;
        }

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * This method handles calling both the trait's alterTable method, and it's parents
     */
    protected function alterTable($table)
    {
        return parent::alterTable($this->traitAlterTable($table));
    }
}
