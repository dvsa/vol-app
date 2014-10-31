<?php

/**
 * External Generic Vehicle trait
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

/**
 * External Generic Vehicle trait
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
trait ExternalGenericVehicleControllerTrait
{
    public function alterActionForm($form)
    {
        var_dump("HI"); die();
        $this->getServiceLocator()->get('Helper\Form')
            ->remove($form, 'licence-vehicle->receivedDate');

        return $form;
    }
}
