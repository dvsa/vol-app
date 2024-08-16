<?php

namespace Olcs\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

class VehiclesVehicle
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function alterForm($form): void
    {
        $this->formHelper->remove($form, 'licence-vehicle->specifiedDate');
        $this->formHelper->remove($form, 'licence-vehicle->removalDate');
        $this->formHelper->remove($form, 'licence-vehicle->discNo');
    }
}
