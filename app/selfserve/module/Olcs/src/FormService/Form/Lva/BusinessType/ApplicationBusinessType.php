<?php

namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\ApplicationBusinessType as CommonApplicationBusinessType;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessType extends CommonApplicationBusinessType
{
    use ButtonsAlterations;

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
        parent::__construct($formHelper, $authService, $guidanceHelper, $formServiceLocator);
    }

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    #[\Override]
    protected function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        if ($params['inForceLicences'] || $params['hasOrganisationSubmittedLicenceApplication']) {
            $this->removeFormAction($form, 'cancel');
            $form->get('form-actions')->get('save')->setLabel('lva.external.return.link');
            $form->get('form-actions')->get('save')->removeAttribute('class');
            $form->get('form-actions')->get('save')->setAttribute('class', 'govuk-button govuk-button--secondary');

            $this->lockForm($form, false);
        } else {
            $this->alterButtons($form);
        }
    }
}
