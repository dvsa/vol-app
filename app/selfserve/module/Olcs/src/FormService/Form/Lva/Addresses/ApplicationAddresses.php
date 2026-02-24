<?php

namespace Olcs\FormService\Form\Lva\Addresses;

use Common\FormService\Form\Lva\Addresses as CommonAddress;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application address
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationAddresses extends CommonAddress
{
    use ButtonsAlterations;

    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Make form alterations
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    #[\Override]
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);
        $this->alterButtons($form);
    }
}
