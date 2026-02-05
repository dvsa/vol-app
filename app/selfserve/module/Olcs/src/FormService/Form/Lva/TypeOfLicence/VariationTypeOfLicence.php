<?php

namespace Olcs\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\Form\Lva\TypeOfLicence\VariationTypeOfLicence as CommonLicenceTypeOfLicence;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Type Of Licence Form
 *
 * @author Alex Peshkov <alex.pehkov@valtech.co.uk>
 */
class VariationTypeOfLicence extends CommonLicenceTypeOfLicence
{
    public function __construct(FormHelperService $formHelper, AuthorizationService $authService, FormServiceManager $formServiceLocator)
    {
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
    #[\Override]
    public function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);

        if (!$params['canUpdateLicenceType']) {
            $this->formHelper->remove($form, 'form-actions');
        } else {
            $this->formHelper->remove($form, 'form-actions->cancel');
        }
    }
}
