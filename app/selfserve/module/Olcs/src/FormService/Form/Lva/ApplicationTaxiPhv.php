<?php

/**
 * Application Taxi Phv
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\TaxiPhv as CommonTaxiPhv;

/**
 * Application Taxi Phv
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTaxiPhv extends CommonTaxiPhv
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
        $this->addBackToOverviewLink($form, 'application', false);

        return $form;
    }
}
