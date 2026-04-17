<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\BusinessDetails\ApplicationBusinessDetails as CommonApplicationBusinessDetails;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;

/**
 * Application Business Details Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationBusinessDetails extends CommonApplicationBusinessDetails
{
    protected FormServiceManager $formServiceLocator;
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper, FormServiceManager $formServiceLocator)
    {
        parent::__construct($formHelper, $formServiceLocator);
    }

    #[\Override]
    public function alterForm($form, $params)
    {
        parent::alterForm($form, $params);
        $this->formHelper->remove($form, 'allow-email');
    }
}
