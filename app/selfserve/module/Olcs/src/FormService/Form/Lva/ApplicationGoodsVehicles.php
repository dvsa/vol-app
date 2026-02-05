<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\FormService\Form\Lva\ApplicationGoodsVehicles as CommonGoodsVehicles;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Goods vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationGoodsVehicles extends CommonGoodsVehicles
{
    use ButtonsAlterations;

    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        FormServiceManager $formServiceLocator
    ) {
        parent::__construct($formHelper, $authService, $formServiceLocator);
    }

    /**
     * Make form alterations
     *
     * @param Form $form form
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->alterButtons($form);

        return $form;
    }
}
