<?php

namespace Olcs\FormService\Form\Lva\GoodsVehicles;

use Common\Service\Helper\FormHelperService;

/**
 * Add Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddVehicle
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function getForm($request, $params = [])
    {
        $form = $this->formHelper->createFormWithRequest('Lva\AddGoodsVehicle', $request);

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
