<?php

namespace Olcs\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\TypeOfLicence\VariationTypeOfLicence as CommonLicenceTypeOfLicence;
use Zend\Form\Form;

/**
 * Licence Type Of Licence Form
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class VariationTypeOfLicence extends CommonLicenceTypeOfLicence
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

        if (!$params['canUpdateLicenceType']) {
            $this->getFormHelper()->remove($form, 'form-actions');
        } else {
            $this->getFormHelper()->remove($form, 'form-actions->cancel');
        }
    }
}
