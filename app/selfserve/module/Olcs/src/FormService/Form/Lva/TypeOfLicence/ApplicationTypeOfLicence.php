<?php

namespace Olcs\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence as CommonLicenceTypeOfLicence;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Type Of Licence Form
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class ApplicationTypeOfLicence extends CommonLicenceTypeOfLicence
{
    use ButtonsAlterations;

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
        $this->alterButtons($form);
    }
}
