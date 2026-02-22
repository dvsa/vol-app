<?php

namespace Olcs\FormService\Form\Lva\People\SoleTrader;

use Common\FormService\Form\Lva\People\SoleTrader\ApplicationSoleTrader as CommonApplicationSoleTrader;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;
use Laminas\Form\Form;

/**
 * Application Sole Trader
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationSoleTrader extends CommonApplicationSoleTrader
{
    use ButtonsAlterations;

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return Form
     */
    #[\Override]
    protected function alterForm($form, array $params)
    {
        $form = parent::alterForm($form, $params);

        if ($params['canModify'] === false) {
            $this->removeFormAction($form, 'cancel');
            $form->get('form-actions')->get('save')->setLabel('lva.external.return.link');
            $form->get('form-actions')->get('save')->removeAttribute('class');
            $form->get('form-actions')->get('save')->setAttribute('class', 'govuk-button govuk-button--secondary');
        } else {
            $this->alterButtons($form);
        }

        return $form;
    }
}
