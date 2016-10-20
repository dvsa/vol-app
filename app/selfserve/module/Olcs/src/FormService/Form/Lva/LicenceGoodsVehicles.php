<?php

namespace Olcs\FormService\Form\Lva;

use Common\Form\Form;

/**
 * Licence Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceGoodsVehicles extends \Common\FormService\Form\Lva\LicenceGoodsVehicles
{
    protected $showShareInfo = true;

    /**
     * Alter form
     *
     * @param Form $form form
     *
     * @return void
     */
    public function alterForm($form)
    {
        parent::alterForm($form);
        $this->removeFormAction($form, 'cancel');
    }
}
