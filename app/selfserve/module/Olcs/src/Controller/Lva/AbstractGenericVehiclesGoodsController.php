<?php

/**
 * Abstract Generic Goods Vehicles Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractVehiclesGoodsController;

/**
 * Abstract Generic Goods Vehicles Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGenericVehiclesGoodsController extends AbstractVehiclesGoodsController
{
    protected function alterTable($table)
    {
        $table->removeAction('print-vehicles');

        return $table;
    }
}
