<?php

namespace Olcs\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence as CommonLicenceTypeOfLicence;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Type Of Licence Form
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class ApplicationTypeOfLicence extends CommonLicenceTypeOfLicence
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected GuidanceHelperService $guidanceHelper;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        AuthorizationService $authService,
        GuidanceHelperService $guidanceHelper,
        FormServiceManager $formServiceLocator
    ) {
        $this->guidanceHelper = $guidanceHelper;
        parent::__construct($formHelper, $authService, $formServiceLocator);
    }
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    public function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);
        $form->get('form-actions')->get('saveAndContinue')->setLabel('lva.external.save_and_continue.button');
        $this->formHelper->remove($form, 'form-actions->save');
    }
}
