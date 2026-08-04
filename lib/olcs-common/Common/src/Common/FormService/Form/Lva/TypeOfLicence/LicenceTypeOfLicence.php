<?php

namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Type Of Licence
 */
class LicenceTypeOfLicence extends AbstractTypeOfLicence
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected FormServiceManager $formServiceLocator)
    {
    }

    #[\Override]
    protected function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);

        $this->formServiceLocator->get('lva-licence')->alterForm($form);

        $this->lockElements($form, $params);
    }

    #[\Override]
    protected function allElementsLocked(Form $form)
    {
        $this->removeStandardFormActions($form);
        $this->addBackToOverviewLink($form, 'licence');
    }
}
