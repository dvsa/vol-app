<?php

/**
 * Edit Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\GoodsVehicles;

use Common\FormService\Form\AbstractFormService;

/**
 * Edit Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class EditVehicle extends AbstractFormService
{
    public function getForm($request)
    {
        return $this->getFormHelper()->createFormWithRequest('Lva\EditGoodsVehicle', $request);
    }
}
