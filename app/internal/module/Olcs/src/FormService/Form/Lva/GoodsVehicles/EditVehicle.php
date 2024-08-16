<?php

namespace Olcs\FormService\Form\Lva\GoodsVehicles;

use Common\Service\Helper\FormHelperService;

/**
 * Edit Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class EditVehicle
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function getForm($request, $params = [])
    {
        $form = $this->formHelper->createFormWithRequest('Lva\EditGoodsVehicle', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm(\Laminas\Form\Form $form, $params)
    {
        if ($params['isRemoved']) {
            $this->formHelper->disableElements($form->get('data'));
            $this->formHelper->disableElements($form->get('licence-vehicle'));

            $this->formHelper->enableElements($form->get('licence-vehicle')->get('removalDate'));
            $this->formHelper->enableElements($form->get('data')->get('version'));
            $form->get('licence-vehicle')->get('removalDate')->setShouldCreateEmptyOption(false);
        }
    }
}
