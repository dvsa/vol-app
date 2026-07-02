<?php

namespace Common\FormService\Form\Continuation;

use Common\Form\Form;
use Common\Form\Model\Form\Continuation\ConditionsUndertakings as ConditionsUndertakingsForm;
use Common\Service\Helper\FormHelperService;

/**
 * Continuation conditions / undertakings form
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ConditionsUndertakings
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
        return $this->formHelper->createForm(ConditionsUndertakingsForm::class);
    }
}
