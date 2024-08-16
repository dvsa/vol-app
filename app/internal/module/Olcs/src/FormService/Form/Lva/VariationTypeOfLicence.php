<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Common\FormService\Form\Lva\TypeOfLicence\VariationTypeOfLicence as CommonVariationTypeOfLicence;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Type Of Licence
 */
class VariationTypeOfLicence extends CommonVariationTypeOfLicence
{
    protected FormHelperService $formHelper;
    protected AuthorizationService $authService;
    protected FormServiceManager $formServiceLocator;

    public function __construct(FormHelperService $formHelper, AuthorizationService $authService, FormServiceManager $formServiceLocator)
    {
        parent::__construct($formHelper, $authService, $formServiceLocator);
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form
     * @param array $params
     * @return \Laminas\Form\Form
     */
    protected function alterForm(Form $form, $params = [])
    {
        parent::alterForm($form, $params);

        if ($form->has('form-actions')) {
            $form->get('form-actions')->get('save')->setLabel('internal.save.button');
        }

        $form->get('type-of-licence')->remove('difference'); // removes guidance text

        return $form;
    }

    protected function allElementsLocked(Form $form)
    {
        $form->remove('form-actions');
    }
}
