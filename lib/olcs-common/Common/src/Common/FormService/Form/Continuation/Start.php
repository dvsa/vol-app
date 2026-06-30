<?php

namespace Common\FormService\Form\Continuation;

use Common\Form\Form;
use Common\Form\Model\Form\Continuation\Start as StartForm;
use Common\Service\Helper\FormHelperService;

/**
 * Continuation start form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Start
{
    public function __construct(protected FormHelperService $formHelper)
    {
    }

    /**
     * Get form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->formHelper->createForm(StartForm::class);
    }
}
