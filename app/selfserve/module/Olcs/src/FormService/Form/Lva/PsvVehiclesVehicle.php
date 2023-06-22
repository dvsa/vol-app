<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\AbstractFormService;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use ZfcRbac\Service\AuthorizationService;

/**
 * Psv Vehicles Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PsvVehiclesVehicle
{
    protected FormHelperService $formHelper;
    protected FormServiceManager $formServiceLocator;

    public function __construct(FormHelperService $formHelper, FormServiceManager $formServiceLocator)
    {
        $this->formHelper = $formHelper;
        $this->formServiceLocator = $formServiceLocator;
    }
    public function alterForm($form)
    {
        $this->formServiceLocator->get('lva-vehicles-vehicle')->alterForm($form);

        $this->formHelper->remove($form, 'licence-vehicle->receivedDate');
    }
}
