<?php

namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\FormServiceManager;
use Common\Rbac\Service\Permission;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;

/**
 * Application Type Of Licence
 */
class ApplicationTypeOfLicence extends AbstractTypeOfLicence
{
    public function __construct(protected FormHelperService $formHelper, protected Permission $permissionService, protected FormServiceManager $formServiceLocator)
    {
    }

    #[\Override]
    protected function alterForm(Form $form, $params = [])
    {
        if ($this->permissionService->isInternalReadOnly()) {
            $this->disableLicenceType($form);
        }

        parent::alterForm($form, $params);

        $this->formServiceLocator->get('lva-application')->alterForm($form);
    }
}
