<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\TaxiPhv as CommonTaxiPhv;
use Common\Form\Form;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Taxi PHV Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTaxiPhv extends CommonTaxiPhv
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
    public function alterForm($form, $params = [])
    {
        parent::alterForm($form, $params);
        $this->removeFormAction($form, 'save');
    }
}
