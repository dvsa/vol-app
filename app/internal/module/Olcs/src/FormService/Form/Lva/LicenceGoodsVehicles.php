<?php

/**
 * Licence Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

/**
 * Licence Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceGoodsVehicles extends \Common\FormService\Form\Lva\LicenceGoodsVehicles
{
    protected function alterForm($form)
    {
        parent::alterForm($form);

        $this->removeStandardFormActions($form);
    }
}
