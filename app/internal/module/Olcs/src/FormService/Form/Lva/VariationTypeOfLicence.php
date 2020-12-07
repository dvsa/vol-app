<?php

namespace Olcs\FormService\Form\Lva;

use Laminas\Form\Form;
use Common\FormService\Form\Lva\TypeOfLicence\VariationTypeOfLicence as CommonVariationTypeOfLicence;

/**
 * Variation Type Of Licence
 */
class VariationTypeOfLicence extends CommonVariationTypeOfLicence
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
