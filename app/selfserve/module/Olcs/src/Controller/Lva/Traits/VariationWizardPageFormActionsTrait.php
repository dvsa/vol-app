<?php

namespace Olcs\Controller\Lva\Traits;

use Zend\Form\FieldsetInterface;
use Zend\Form\FormInterface;

trait VariationWizardPageFormActionsTrait
{
    abstract public function getSubmitActionText();

    protected function alterFormForLva(FormInterface $form, $data = null)
    {
        /** @var FieldsetInterface $formActions */
        $formActions = $form->get('form-actions');
        $formActions->remove('save');
        $formActions->get('saveAndContinue')->setLabel($this->getSubmitActionText());
    }
}
