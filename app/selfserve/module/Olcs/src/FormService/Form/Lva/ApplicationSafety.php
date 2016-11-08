<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Safety;
use Olcs\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application safety
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationSafety extends Safety
{
    use ButtonsAlterations;

    /**
     * Returns form
     *
     * @return \Zend\Form\FormInterface
     */
    public function getForm()
    {
        $form = $this->getFormHelper()->createForm('Lva\Safety');

        $this->alterButtons($form);

        return $form;
    }
}
