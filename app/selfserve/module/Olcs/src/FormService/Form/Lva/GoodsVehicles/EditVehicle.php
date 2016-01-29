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
    public function getForm($request, $params = [])
    {
        $form = $this->getFormHelper()->createFormWithRequest('Lva\EditGoodsVehicle', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm($form, $params)
    {
        if ($params['isRemoved']) {
            $this->getFormHelper()->disableElements($form->get('data'));
            $this->getFormHelper()->disableElements($form->get('licence-vehicle'));
            $this->getFormHelper()->remove($form, 'form-actions->submit');
        }
    }
}
