<?php

namespace Olcs\FormService\Licence\Surrender;

use Common\FormService\Form\Lva\Addresses as CommonAddress;
use Zend\Form\Form;

class Addresses extends CommonAddress
{
    /**
     * Return form
     *
     * @param array $params Parameters
     *
     * @return \Zend\Form\Form
     */
    public function getForm(array $params = null)
    {
        $form = $this->getFormHelper()->createForm('Lva\Addresses');

        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * Make form alterations
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return Form
     */
    protected function alterForm(Form $form, array $params = null): Form
    {
        $fieldsetsToRemove = [
            'consultant',
            'establishment_address',
            'establishment',
            'consultantAddress',
            'consultantContact',
        ];

        $this->getFormHelper()->removeFieldsets($form, $fieldsetsToRemove);
        $this->getFormHelper()->removeFieldList($form, 'form-actions', ['cancel', 'saveAndContinue']);
        $form->get('form-actions')->get('save')->setAttribute('class', 'action--primary large');

        return $form;
    }
}
