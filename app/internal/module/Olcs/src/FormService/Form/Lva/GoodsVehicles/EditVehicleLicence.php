<?php

/**
 * Edit Vehicle Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\GoodsVehicles;

use Common\FormService\Form\AbstractFormService;

/**
 * Edit Vehicle Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class EditVehicleLicence extends AbstractFormService
{
    public function getForm($request, $params = [])
    {
        return $this->getFormHelper()->createFormWithRequest('Lva\EditGoodsVehicleLicence', $request);
    }
}
