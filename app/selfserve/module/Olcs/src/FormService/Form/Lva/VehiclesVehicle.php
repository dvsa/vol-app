<?php

/**
 * Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;

/**
 * Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesVehicle extends AbstractFormService
{
    public function alterForm($form)
    {
        $this->getFormHelper()->remove($form, 'licence-vehicle->specifiedDate');
        $this->getFormHelper()->remove($form, 'licence-vehicle->removalDate');
        $this->getFormHelper()->remove($form, 'licence-vehicle->discNo');
    }
}
