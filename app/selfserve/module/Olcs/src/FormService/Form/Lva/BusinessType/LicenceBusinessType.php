<?php

/**
 * Licence Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\LicenceBusinessType as CommonLicenceBusinessType;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Licence Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessType extends CommonLicenceBusinessType
{
    use ServiceLocatorAwareTrait;

    public function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        $this->lockForm($form);
    }
}
