<?php

/**
 * Application Psv Vehicles
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\PsvVehicles as CommonPsvVehicles;

/**
 * Application Psv Vehicles
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationPsvVehicles extends CommonPsvVehicles
{
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
