<?php

namespace Olcs\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

class LockBusinessDetails
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function alterForm($form): void
    {
        $fieldset = $form->get('data');

        if ($fieldset->has('companyNumber')) {
            $this->formHelper->lockElement(
                $fieldset->get('companyNumber'),
                'business-details.company_number.locked'
            );
            $this->formHelper->disableElement($form, 'data->companyNumber->company_number');
            $this->formHelper->disableElement($form, 'data->companyNumber->submit_lookup_company');
        }

        if ($fieldset->has('name')) {
            $this->formHelper->lockElement($fieldset->get('name'), 'business-details.name.locked');
            $this->formHelper->disableElement($form, 'data->name');
        }
    }
}
