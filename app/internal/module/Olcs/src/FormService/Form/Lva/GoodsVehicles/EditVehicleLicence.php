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
        $form = $this->getFormHelper()->createFormWithRequest('Lva\EditGoodsVehicleLicence', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm($form, $params)
    {
        if ($params['isRemoved']) {
            $this->getFormHelper()->disableElements($form->get('data'));
            $this->getFormHelper()->disableElements($form->get('licence-vehicle'));

            $this->getFormHelper()->enableElements($form->get('licence-vehicle')->get('removalDate'));
            $this->getFormHelper()->enableElements($form->get('data')->get('version'));
            $form->get('licence-vehicle')->get('removalDate')->setShouldCreateEmptyOption(false);
        }
    }
}
