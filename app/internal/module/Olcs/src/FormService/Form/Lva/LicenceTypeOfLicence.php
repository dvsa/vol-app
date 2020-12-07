<?php

namespace Olcs\FormService\Form\Lva;

use Laminas\Form\Form;
use Common\FormService\Form\Lva\TypeOfLicence\LicenceTypeOfLicence as CommonLicenceTypeOfLicence;

/**
 * Licence Type Of Licence
 */
class LicenceTypeOfLicence extends CommonLicenceTypeOfLicence
{
    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @param array $params
     * @return \Laminas\Form\Form
     */
    protected function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);

        if ($form->has('form-actions')) {
            $form->get('form-actions')->get('save')->setLabel('internal.save.button');
        }

        $form->get('type-of-licence')->remove('difference'); // removes guidance text

        return $form;
    }

    protected function allElementsLocked(Form $form)
    {
        $form->remove('form-actions');
    }
}
