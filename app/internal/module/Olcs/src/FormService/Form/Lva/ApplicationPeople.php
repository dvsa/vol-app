<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\People\ApplicationPeople as CommonApplicationPeople;
use Common\Form\Form;

/**
 * Application People Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationPeople extends CommonApplicationPeople
{
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    public function alterForm($form, $params = [])
    {
        parent::alterForm($form, $params);
        $this->removeFormAction($form, 'save');
    }
}
