<?php

namespace Olcs\Controller\Lva\Traits;

use Common\Form\Model\Form\Lva\Fieldset\FormActions;
use Zend\Form\FieldsetInterface;
use Zend\Form\FormInterface;

/**
 * Trait for use in an AbstractController that forms part of a variation wizard whose form uses a the standard set of
 * lva actions which need to be modified to perform actions relevant to a wizard
 *
 * @see FormActions - this trait operates on forms which have a fieldset like this under 'form-actions'
 */
trait VariationWizardPageFormActionsTrait
{
    /**
     * Get the text (or translation string) for the saveAndContinue button
     *
     * @return string
     */
    abstract public function getSubmitActionText();

    /**
     * Alter form actions in such a way that they will work in a wizard
     *
     * Typically this will override the function from AbstractController
     *
     * @param FormInterface $form the form
     * @param null          $data the form data
     *
     * @return void
     */
    protected function alterFormForLva(FormInterface $form, $data = null)
    {
        /** @var FieldsetInterface $formActions */
        $formActions = $form->get('form-actions');
        $formActions->remove('save');
        $formActions->get('saveAndContinue')->setLabel($this->getSubmitActionText());
    }
}
