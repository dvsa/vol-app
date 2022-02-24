<?php

namespace Olcs\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\TypeOfLicence\LicenceTypeOfLicence as CommonLicenceTypeOfLicence;
use Laminas\Form\Form;

/**
 * Licence Type Of Licence Form
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class LicenceTypeOfLicence extends CommonLicenceTypeOfLicence
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

        $this->getFormHelper()->remove($form, 'form-actions');
    }
}
