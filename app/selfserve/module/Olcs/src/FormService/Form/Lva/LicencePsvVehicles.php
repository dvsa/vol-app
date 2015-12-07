<?php

/**
 * Licence Psv Vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

/**
 * Licence Psv Vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicencePsvVehicles extends PsvVehicles
{
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->removeFormAction($form, 'saveAndContinue');
    }
}
