<?php

namespace Olcs\FormService\Form\Lva\Addresses;

use Common\FormService\Form\Lva\Addresses as CommonAddress;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;

/**
 * Licence address
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceAddresses extends CommonAddress
{
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
     * @return Form
     */
    #[\Override]
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);
        $form->get('form-actions')->get('save')->setAttribute('class', 'govuk-button');
        $this->formHelper->remove($form, 'form-actions->cancel');

        return $form;
    }
}
