<?php

namespace Olcs\FormService\Form\Lva\Traits;

use Common\Form\Form;

/**
 * Buttons alterations
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait ButtonsAlterations
{
    /**
     * Alter buttons
     *
     * @param Form $form form
     *
     * @return void
     */
    protected function alterButtons($form)
    {
        $form->get('form-actions')->get('saveAndContinue')->setLabel('lva.external.save_and_continue.button');
        $form->get('form-actions')->get('save')->setLabel('lva.external.save_and_return.link');
        $form->get('form-actions')->get('save')->removeAttribute('class');
        $form->get('form-actions')->get('save')->setAttribute('class', 'action--tertiary large');
        $this->getFormHelper()->remove($form, 'form-actions->cancel');
    }
}
