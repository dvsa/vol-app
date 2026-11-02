<?php

/**
 * GracePeriod.php
 */

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

#[Form\Name('GracePeriod')]
#[Form\Options(['prefer_form_input_filter' => true])]
#[Form\Attributes(['method' => 'post'])]
class GracePeriod
{
    #[Form\Name('details')]
    #[Form\Options(['label' => '', 'class' => ''])]
    #[Form\ComposedObject('Olcs\Form\Model\Fieldset\GracePeriodDetails')]
    public $details = null;

    #[Form\Name('form-actions')]
    #[Form\ComposedObject('Common\Form\Model\Form\Lva\Fieldset\FormCrudActions')]
    #[Form\Attributes(['class' => 'govuk-button-group'])]
    public $formActions = null;
}
