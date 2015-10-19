<?php

/**
 * Licence Taxi Phv
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\LicenceTaxiPhv as CommonLicenceTaxiPhv;

/**
 * Licence Taxi Phv
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTaxiPhv extends CommonLicenceTaxiPhv
{
    /**
     * Make form alterations
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->addBackToOverviewLink($form, 'licence');

        return $form;
    }
}
