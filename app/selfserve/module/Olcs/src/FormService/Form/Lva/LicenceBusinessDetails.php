<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\LicenceBusinessDetails as CommonLicenceBusinessDetails;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;

/**
 * Licence Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessDetails extends CommonLicenceBusinessDetails
{
    protected FormServiceManager $formServiceLocator;
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper, FormServiceManager $formServiceLocator)
    {
        parent::__construct($formHelper, $formServiceLocator);
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
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);

        $this->formServiceLocator->get('lva-lock-business_details')->alterForm($form);
        $this->formHelper->remove($form, 'form-actions->cancel');
    }
}
