<?php

namespace Common\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Goods Vehicles Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationGoodsVehicles extends AbstractGoodsVehicles
{
    public function __construct(FormHelperService $formHelper, AuthorizationService $authService, private FormServiceManager $formServiceLocator)
    {
        $this->formHelper = $formHelper;
        $this->authService = $authService;
    }

    /**
     * @return void
     */
    #[\Override]
    protected function alterForm($form)
    {
        $this->removeStandardFormActions($form);
        $this->formServiceLocator->get('lva-licence-variation-vehicles')->alterForm($form);
    }
}
