<?php

namespace Common\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Goods Vehicles Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationGoodsVehicles extends AbstractGoodsVehicles
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, private FormServiceManager $formServiceLocator)
    {
    }

    /**
     * @return void
     */
    #[\Override]
    protected function alterForm($form)
    {
        $this->formServiceLocator->get('lva-application')->alterForm($form);
    }
}
