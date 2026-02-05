<?php

namespace Olcs\FormService\Form\Lva;

use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceGoodsVehicles extends \Common\FormService\Form\Lva\LicenceGoodsVehicles
{
    protected $showShareInfo = true;

    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected FormServiceManager $formServiceLocator;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService, FormServiceManager $formServiceLocator)
    {
        parent::__construct($formHelper, $authService, $formServiceLocator);
    }

    /**
     * Alter form
     *
     * @param Form $form form
     *
     * @return void
     */
    #[\Override]
    public function alterForm($form)
    {
        parent::alterForm($form);
        $this->removeFormAction($form, 'cancel');
    }
}
