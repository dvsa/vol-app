<?php

namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\LicenceBusinessType as CommonLicenceBusinessType;
use Zend\Form\Form;

/**
 * Licence Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessType extends CommonLicenceBusinessType
{
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    public function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        $this->lockForm($form);
        $this->getFormHelper()->remove($form, 'form-actions');
    }
}
