<?php

/**
 * Licence Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\LicenceSoleTrader as CommonLicenceSoleTrader;

/**
 * Licence Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceSoleTrader extends CommonLicenceSoleTrader
{
    public function alterForm($form, array $params)
    {
        $form = parent::alterForm($form, $params);

        $this->removeStandardFormActions($form);
        $this->addBackToOverviewLink($form, 'licence');

        return $form;
    }
}
