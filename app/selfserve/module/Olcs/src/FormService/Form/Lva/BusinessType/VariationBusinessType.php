<?php

namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\VariationBusinessType as CommonVariationBusinessType;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessType extends CommonVariationBusinessType
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
    public function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        $this->lockForm($form);
        $this->formHelper->remove($form, 'form-actions');
    }
}
