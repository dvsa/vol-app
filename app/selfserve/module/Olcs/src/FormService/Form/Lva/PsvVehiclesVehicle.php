<?php

/**
 * Psv Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;

/**
 * Psv Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesVehicle extends AbstractFormService
{
    public function alterForm($form)
    {
        $this->getFormServiceLocator()->get('lva-vehicles-vehicle')->alterForm($form);

        $this->getFormHelper()->remove($form, 'licence-vehicle->receivedDate');
    }
}
