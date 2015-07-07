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
    public function getForm($request, $params = [])
    {
        $form = $this->getFormHelper()->createFormWithRequest('Lva\AddGoodsVehicle', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm($form, $params)
    {
        if ($params['spacesRemaining'] < 2) {
            $form->get('form-actions')->remove('addAnother');
        }
    }
}
