<?php

namespace Olcs\FormService\Form\Lva\GoodsVehicles;

use Common\Service\Helper\FormHelperService;

/**
 * Add Vehicle Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddVehicleLicence
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    public function getForm($request, $params = [])
    {
        $form = $this->formHelper->createFormWithRequest('Lva\AddGoodsVehicleLicence', $request);

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
