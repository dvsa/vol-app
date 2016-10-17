<?php

namespace Olcs\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence as CommonLicenceTypeOfLicence;
use Zend\Form\Form;

/**
 * Application Type Of Licence Form
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class ApplicationTypeOfLicence extends CommonLicenceTypeOfLicence
{
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    public function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);
        $form->get('form-actions')->get('saveAndContinue')->setLabel('lva.external.save_and_continue.button');
        $this->getFormHelper()->remove($form, 'form-actions->save');
    }
}
