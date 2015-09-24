<?php

/**
 * Variation SoleTrader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\VariationSoleTrader as CommonVariationSoleTrader;

/**
 * Variation SoleTrader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationSoleTrader extends CommonVariationSoleTrader
{
    public function alterForm($form, array $params)
    {
        $form = parent::alterForm($form, $params);

        $this->removeStandardFormActions($form);
        $this->addBackToOverviewLink($form, 'variation');

        return $form;
    }
}
