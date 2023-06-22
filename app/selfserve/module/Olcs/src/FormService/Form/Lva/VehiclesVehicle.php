<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;
use Common\Service\Helper\FormHelperService;

/**
 * Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesVehicle
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    public function alterForm($form)
    {
        $this->formHelper->remove($form, 'licence-vehicle->specifiedDate');
        $this->formHelper->remove($form, 'licence-vehicle->removalDate');
        $this->formHelper->remove($form, 'licence-vehicle->discNo');
    }
}
