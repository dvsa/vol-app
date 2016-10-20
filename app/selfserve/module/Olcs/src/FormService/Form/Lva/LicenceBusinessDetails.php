<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\LicenceBusinessDetails as CommonLicenceBusinessDetails;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Form\Form;

/**
 * Licence Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessDetails extends CommonLicenceBusinessDetails implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->getFormServiceLocator()->get('lva-lock-business_details')->alterForm($form);
        $this->getFormHelper()->remove($form, 'form-actions->cancel');
    }
}
