<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Psv Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesVehicle
{
    public function __construct(protected FormHelperService $formHelper, protected FormServiceManager $formServiceLocator)
    {
    }
    public function alterForm($form): void
    {
        $this->formServiceLocator->get('lva-vehicles-vehicle')->alterForm($form);

        $this->formHelper->remove($form, 'licence-vehicle->receivedDate');
    }
}
