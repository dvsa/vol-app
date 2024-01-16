<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\PsvVehicles as CommonPsvVehicles;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Psv Vehicles
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationPsvVehicles extends CommonPsvVehicles
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService)
    {
        parent::__construct($formHelper, $authService);
    }

    protected function alterForm($form)
    {
        parent::alterForm($form);

        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        return $form;
    }
}
