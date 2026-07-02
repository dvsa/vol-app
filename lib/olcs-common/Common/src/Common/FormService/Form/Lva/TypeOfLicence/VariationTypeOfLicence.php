<?php

namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Type Of Licence
 */
class VariationTypeOfLicence extends AbstractTypeOfLicence
{
    public function __construct(protected FormHelperService $formHelper, protected AuthorizationService $authService, protected FormServiceManager $formServiceLocator)
    {
    }

    #[\Override]
    protected function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);

        $this->formServiceLocator->get('lva-variation')->alterForm($form);

        $this->lockElements($form, $params);

        $licenceTypeFieldset = $form->get('type-of-licence')->get('licence-type');

        $this->formHelper->setCurrentOption(
            $licenceTypeFieldset->get('licence-type'),
            $params['currentLicenceType']
        );

        $this->formHelper->setCurrentOption(
            $licenceTypeFieldset->get('ltyp_siContent')->get('vehicle-type'),
            $params['currentVehicleType']
        );
    }

    #[\Override]
    protected function allElementsLocked(Form $form)
    {
        $this->removeStandardFormActions($form);
        $this->addBackToOverviewLink($form, 'variation');
    }
}
