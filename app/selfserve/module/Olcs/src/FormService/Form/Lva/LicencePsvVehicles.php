<?php

namespace Olcs\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Psv Vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicencePsvVehicles extends PsvVehicles
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
     * @param Form $form form
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form)
    {
        parent::alterForm($form);
        $this->removeFormAction($form, 'saveAndContinue');
        $this->removeFormAction($form, 'cancel');
        $form->get('form-actions')->get('save')->setAttribute('class', 'govuk-button');
        return $form;
    }
}
