<?php

/**
 * Licence Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\LicenceBusinessDetails as CommonLicenceBusinessDetails;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Licence Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessDetails extends CommonLicenceBusinessDetails implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->getFormServiceLocator()->get('lva-lock-business_details')->alterForm($form);
    }
}
