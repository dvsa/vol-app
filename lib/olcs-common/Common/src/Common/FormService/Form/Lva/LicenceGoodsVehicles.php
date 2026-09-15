<?php

namespace Common\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Goods Vehicles Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceGoodsVehicles extends AbstractGoodsVehicles
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected FormServiceManager $formServiceLocator)
    {
    }

    /**
     * @return void
     */
    #[\Override]
    protected function alterForm($form)
    {
        $this->formServiceLocator->get('lva-licence')->alterForm($form);
        $this->formServiceLocator->get('lva-licence-variation-vehicles')->alterForm($form);
        $this->showShareInfo = true;
    }
}
