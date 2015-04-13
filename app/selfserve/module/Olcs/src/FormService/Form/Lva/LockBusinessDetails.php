<?php

/**
 * Lock Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;

/**
 * Licence Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LockBusinessDetails extends AbstractFormService
{
    public function alterForm($form)
    {
        $fieldset = $form->get('data');

        if ($fieldset->has('companyNumber')) {
            $this->getFormHelper()->lockElement(
                $fieldset->get('companyNumber'),
                'business-details.company_number.locked'
            );
            $this->getFormHelper()->disableElement($form, 'data->companyNumber->company_number');
            $this->getFormHelper()->disableElement($form, 'data->companyNumber->submit_lookup_company');
        }

        if ($fieldset->has('name')) {
            $this->getFormHelper()->lockElement($fieldset->get('name'), 'business-details.name.locked');
            $this->getFormHelper()->disableElement($form, 'data->name');
        }
    }
}
