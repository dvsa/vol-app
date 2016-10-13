<?php

namespace Olcs\FormService\Form\Lva\BusinessType;

use Common\FormService\Form\Lva\BusinessType\ApplicationBusinessType as CommonApplicationBusinessType;
use Zend\Form\Form;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Business Type Form
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessType extends CommonApplicationBusinessType
{
    use ButtonsAlterations;

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     */
    protected function alterForm(Form $form, $params)
    {
        parent::alterForm($form, $params);

        if ($params['inForceLicences']) {
            $this->removeFormAction($form, 'cancel');
            $form->get('form-actions')->get('save')->setLabel('lva.external.return.link');
            $form->get('form-actions')->get('save')->removeAttribute('class');
            $form->get('form-actions')->get('save')->setAttribute('class', 'action--tertiary large');

            $this->lockForm($form, false);
        } else {
            $this->alterButtons($form);
        }
    }
}
