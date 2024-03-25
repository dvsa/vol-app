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
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    public function getForm($request, $params = []): \Laminas\Form\FormInterface
    {
        $form = $this->formHelper->createFormWithRequest('Lva\AddGoodsVehicle', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm(\Laminas\Form\FormInterface $form, $params): void
    {
        if ($params['spacesRemaining'] < 2) {
            $form->get('form-actions')->remove('addAnother');
        }
    }
}
