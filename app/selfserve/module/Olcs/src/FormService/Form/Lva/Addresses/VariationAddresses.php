<?php

namespace Olcs\FormService\Form\Lva\Addresses;

use Common\FormService\Form\Lva\Addresses as CommonAddress;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;

/**
 * Variation address
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VariationAddresses extends CommonAddress
{
    protected FormHelperService $formHelper;

    public function __construct(FormHelperService $formHelper)
    {
        parent::__construct($formHelper);
    }

    /**
     * Alter form
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
        $this->formHelper->remove($form, 'form-actions->cancel');

        return $form;
    }
}
