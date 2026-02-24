<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\ApplicationBusinessDetails as CommonApplicationBusinessDetails;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\Form\Form;

/**
 * Application Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetails extends CommonApplicationBusinessDetails
{
    use ButtonsAlterations;

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

        $this->formHelper->remove($form, 'allow-email');

        // if we have got any in force licences or submitted licence application lock the elements down
        if ($params['hasInforceLicences'] || ($params['hasOrganisationSubmittedLicenceApplication'] ?? false)) {
            $this->formServiceLocator->get('lva-lock-business_details')->alterForm($form);
        }
        $this->alterButtons($form);
    }
}
