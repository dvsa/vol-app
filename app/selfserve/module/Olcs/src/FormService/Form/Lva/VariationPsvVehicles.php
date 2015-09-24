<?php

/**
 * Variation Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VariationPsvVehicles as CommonVariationPsvVehicles;

/**
 * Variation Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPsvVehicles extends CommonVariationPsvVehicles
{
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $this->addBackToOverviewLink($form, 'variation');
    }
}
