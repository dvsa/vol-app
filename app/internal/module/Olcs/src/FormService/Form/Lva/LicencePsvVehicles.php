<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\PsvVehicles as CommonPsvVehicles;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Psv Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicencePsvVehicles extends CommonPsvVehicles
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService)
    {
        parent::__construct($formHelper, $authService);
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @return \Laminas\Form\Form
     */
    protected function alterForm($form)
    {
        $this->showShareInfo = true;
        parent::alterForm($form);

        $saveButton = $form->get('form-actions')->get('save');
        $this->setPrimaryAction($form, 'save');
        $this->formHelper->alterElementLabel($saveButton, 'internal.', FormHelperService::ALTER_LABEL_PREPEND);

        return $form;
    }
}
