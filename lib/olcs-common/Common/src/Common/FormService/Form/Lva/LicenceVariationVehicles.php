<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;

/**
 * Licence Variation Vehicles Form
 * - Common logic between goods/psv licence/variation vehicles forms
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceVariationVehicles
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    public function alterForm($form): void
    {
        $this->formHelper->remove($form, 'data->hasEnteredReg');
        $this->formHelper->remove($form, 'data->notice');
    }
}
