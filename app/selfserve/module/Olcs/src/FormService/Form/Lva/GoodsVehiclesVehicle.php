<?php

/**
 * Goods Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;

/**
 * Goods Vehicles Vehicle
 *
 * @NOTE This service is common accross goods and psv
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehiclesVehicle extends AbstractFormService
{
    public function alterForm($form, $params)
    {
        $this->getFormServiceLocator()->get('lva-vehicles-vehicle')->alterForm($form);

        $form->remove('vehicle-history-table');

        $this->getFormHelper()->remove($form, 'licence-vehicle->receivedDate');
    }
}
