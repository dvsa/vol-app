<?php

/**
 * Abstract Generic Goods Vehicles Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractVehiclesController;

/**
 * Abstract Generic Goods Vehicles Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGenericVehiclesGoodsController extends AbstractVehiclesController
{
    /**
     * Alter vehicle form
     *
     * @param Form $form
     * @return Form
     */
    protected function alterVehicleForm($form, $mode)
    {
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'licence-vehicle->receivedDate');

        return parent::alterVehicleForm($form, $mode);
    }

    protected function alterTable($table)
    {
        $table->removeAction('print-vehicles');

        return $table;
    }

    /**
     * Externally we always want to remove the table
     *
     * @param \Zend\Form\Form $form
     * @param string $mode
     */
    protected function alterVehicleFormForLocation($form, $mode)
    {
        $form->remove('vehicle-history-table');
    }
}
