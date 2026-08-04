<?php

namespace Common\FormService\Form\Lva\BusinessDetails;

use Common\FormService\FormServiceInterface;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetails extends AbstractBusinessDetails
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper, protected FormServiceManager $formServiceLocator)
    {
        parent::__construct($formHelper);
    }

    /**
     * @return void
     */
    #[\Override]
    protected function alterForm($form, $params)
    {
        $this->formServiceLocator->get('lva-application')->alterForm($form);
        parent::alterForm($form, $params);
    }
}
