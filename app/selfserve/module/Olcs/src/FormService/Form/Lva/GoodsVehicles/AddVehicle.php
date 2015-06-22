<?php

/**
 * Add Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\GoodsVehicles;

use Common\FormService\Form\AbstractFormService;

/**
 * Add Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddVehicle extends AbstractFormService
{
    public function getForm($request)
    {
        return $this->getFormHelper()->createFormWithRequest('Lva\AddGoodsVehicle', $request);
    }
}
