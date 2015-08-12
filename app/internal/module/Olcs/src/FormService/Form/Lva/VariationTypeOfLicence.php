<?php

namespace Olcs\FormService\Form\Lva;

use Zend\Form\Form;
use Common\FormService\Form\Lva\TypeOfLicence\VariationTypeOfLicence as CommonVariationTypeOfLicence;

/**
 * Variation Type Of Licence
 */
class VariationTypeOfLicence extends CommonVariationTypeOfLicence
{
    /**
     * Make form alterations
     *
     * @param \Zend\Form\Form $form
     * @param array $params
     * @return \Zend\Form\Form
     */
    protected function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');
        $form->get('type-of-licence')->remove('difference'); // removes guidance text

        return $form;
    }
}
