<?php

/**
 * Licence Business Details Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\LicenceBusinessDetails as CommonLicenceBusinessDetails;

/**
 * Licence Business Details Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceBusinessDetails extends CommonLicenceBusinessDetails
{
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);
        $this->getFormHelper()->remove($form, 'allow-email');
    }
}
