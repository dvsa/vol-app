<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\TaxiPhv as CommonTaxiPhv;
use Common\Form\Form;

/**
 * Application Taxi PHV Form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTaxiPhv extends CommonTaxiPhv
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
