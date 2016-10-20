<?php

namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\LicenceSoleTrader as CommonLicenceSoleTrader;
use Common\Form\Form;

/**
 * Licence Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceSoleTrader extends CommonLicenceSoleTrader
{
    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return Form
     */
    public function alterForm($form, array $params)
    {
        $form = parent::alterForm($form, $params);

        $this->removeStandardFormActions($form);

        return $form;
    }
}
