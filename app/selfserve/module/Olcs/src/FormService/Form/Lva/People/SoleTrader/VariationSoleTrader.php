<?php

namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\VariationSoleTrader as CommonVariationSoleTrader;
use Common\Form\Form;

/**
 * Variation SoleTrader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationSoleTrader extends CommonVariationSoleTrader
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
