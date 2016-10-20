<?php

namespace Olcs\FormService\Form\Lva\Addresses;

use Common\FormService\Form\Lva\Addresses as CommonAddress;
use Zend\Form\Form;

/**
 * Variation address
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationAddresses extends CommonAddress
{
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return Form
     */
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);
        $this->getFormHelper()->remove($form, 'form-actions->cancel');

        return $form;
    }
}
