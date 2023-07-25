<?php

namespace Olcs\FormService\Form\Lva\GoodsVehicles;

use Common\FormService\Form\AbstractFormService;
use Common\Service\Helper\FormHelperService;

/**
 * Edit Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class EditVehicle
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    public function getForm($request, $params = [])
    {
        $form = $this->formHelper->createFormWithRequest('Lva\EditGoodsVehicle', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm($form, $params)
    {
        if ($params['isRemoved']) {
            $this->formHelper->disableElements($form->get('data'));
            $this->formHelper->disableElements($form->get('licence-vehicle'));
            $this->formHelper->remove($form, 'form-actions->submit');
        }
    }
}
