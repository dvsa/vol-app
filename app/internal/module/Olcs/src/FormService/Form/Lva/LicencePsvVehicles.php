<?php

/**
 * Licence Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\PsvVehicles as CommonPsvVehicles;

/**
 * Licence Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvVehicles extends CommonPsvVehicles
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

        $this->removeStandardFormActions($form);

        return $form;
    }
}
