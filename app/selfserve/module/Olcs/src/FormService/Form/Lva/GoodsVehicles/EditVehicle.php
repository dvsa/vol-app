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
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function getForm($request, $params = []): \Laminas\Form\FormInterface
    {
        $form = $this->formHelper->createFormWithRequest('Lva\EditGoodsVehicle', $request);

        $this->alterForm($form, $params);

        return $form;
    }

    protected function alterForm(\Laminas\Form\FormInterface $form, $params): void
    {
        if ($params['isRemoved']) {
            $this->formHelper->disableElements($form->get('data'));
            $this->formHelper->disableElements($form->get('licence-vehicle'));
            $this->formHelper->remove($form, 'form-actions->submit');
        }
    }
}
