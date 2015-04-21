<?php

/**
 * Abstract Generic Goods Vehicles Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractVehiclesPsvController;

/**
 * Abstract Generic Goods Vehicles Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGenericVehiclesPsvController extends AbstractVehiclesPsvController
{
    public function alterForm($form, $data)
    {
        $form = parent::alterForm($form, $data);

        $form->get('medium')->get('table')->getTable()->removeAction('edit');
        $form->get('large')->get('table')->getTable()->removeAction('edit');

        return $form;
    }
}
