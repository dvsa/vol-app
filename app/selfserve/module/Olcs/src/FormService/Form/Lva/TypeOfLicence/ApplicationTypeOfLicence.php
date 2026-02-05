<?php

namespace Olcs\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence as CommonLicenceTypeOfLicence;
use Common\FormService\FormServiceManager;
use Common\Rbac\Service\Permission;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Laminas\Form\Form;

class ApplicationTypeOfLicence extends CommonLicenceTypeOfLicence
{
    protected FormHelperService $formHelper;
    protected Permission $permissionService;
    protected FormServiceManager $formServiceLocator;

    public function __construct(
        FormHelperService $formHelper,
        Permission $permissionService,
        protected GuidanceHelperService $guidanceHelper,
        FormServiceManager $formServiceLocator
    ) {
        parent::__construct($formHelper, $permissionService, $formServiceLocator);
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
    public function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);
        $form->get('form-actions')->get('saveAndContinue')->setLabel('lva.external.save_and_continue.button');
        $this->formHelper->remove($form, 'form-actions->save');
    }
}
