<?php

namespace Olcs\FormService\Form\Lva\Addresses;

use Common\FormService\Form\Lva\Addresses as CommonAddress;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application address
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationAddresses extends CommonAddress
{
    use ButtonsAlterations;

    /**
     * Make form alterations
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);
        $this->alterButtons($form);
    }
}
