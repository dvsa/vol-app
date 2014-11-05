<?php

/**
 * Abstract Generic Psv Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractVehiclesPsvController;

/**
 * Abstract Generic Psv Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
abstract class AbstractGenericVehiclesPsvController extends AbstractVehiclesPsvController
{
    /**
     * Alter action form
     *
     * @param Form $form
     * @return Form
     */
    protected function alterVehicleForm($form, $mode)
    {
        $this->getServiceLocator()->get('Helper\Form')->remove($form, 'licence-vehicle->receivedDate');

        return parent::alterVehicleForm($form, $mode);
    }
}
