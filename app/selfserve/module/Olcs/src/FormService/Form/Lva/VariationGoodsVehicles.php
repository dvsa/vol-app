<?php

/**
 * Variation Goods Vehicles Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\VariationGoodsVehicles as CommonVariationGoodsVehicles;

/**
 * Variation Goods Vehicles Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationGoodsVehicles extends CommonVariationGoodsVehicles
{
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->addBackToOverviewLink($form, 'variation');
    }
}
