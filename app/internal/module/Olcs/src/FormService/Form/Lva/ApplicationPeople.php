<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\People\ApplicationPeople as CommonApplicationPeople;
use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application People Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationPeople extends CommonApplicationPeople
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService)
    {
        parent::__construct($formHelper, $authService);
    }

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    public function alterForm(Form $form, array $params = [])
    {
        parent::alterForm($form, $params);
        $this->removeFormAction($form, 'save');
    }
}
