<?php

/**
 * Application Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader as CommonApplicationSoleTrader;

/**
 * Application Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationSoleTrader extends CommonApplicationSoleTrader
{
    protected function alterForm($form, array $params)
    {
        $form = parent::alterForm($form, $params);

        if ($params['canModify'] === false) {
            $this->removeFormAction($form, 'save');
            $this->removeFormAction($form, 'cancel');
            $this->addBackToOverviewLink($form, 'application', false);
        }

        return $form;
    }
}
