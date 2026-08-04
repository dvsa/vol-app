<?php

namespace Common\FormService\Form\Lva\BusinessDetails;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;

/**
 * Variation Business Details Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetails extends AbstractBusinessDetails
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
        $this->formServiceLocator->get('lva-variation')->alterForm($form);

        parent::alterForm($form, $params);
    }
}
