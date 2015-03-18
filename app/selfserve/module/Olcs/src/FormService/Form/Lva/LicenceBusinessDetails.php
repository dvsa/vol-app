<?php

/**
 * Licence Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\LicenceBusinessDetails as CommonLicenceBusinessDetails;

/**
 * Licence Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessDetails extends CommonLicenceBusinessDetails
{
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->getFormServiceLocator()->get('lva-lock-business_details')->alterForm($form);
    }
}
