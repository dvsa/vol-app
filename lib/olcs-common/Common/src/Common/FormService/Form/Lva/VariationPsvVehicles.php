<?php

namespace Common\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPsvVehicles extends PsvVehicles
{
    protected FormHelperService $formHelper;

    protected AuthorizationService $authService;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService)
    {
        parent::__construct($formHelper, $authService);
    }

    #[\Override]
    protected function alterForm($form)
    {
        $this->removeStandardFormActions($form);
        return $form;
    }
}
