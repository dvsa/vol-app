<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;
use Common\Service\Helper\FormHelperService;

/**
 * Licence Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LockBusinessDetails
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    public function alterForm($form)
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
