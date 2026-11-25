<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Operating centre permission fieldset
 */
class OperatingCentrePermission
{
    #[Form\Attributes(['id' => 'permission', 'placeholder' => ''])]
    #[Form\Options(['checked_value' => 'Y', 'unchecked_value' => 'N', 'label' => 'application_operating-centres_authorisation-sub-action.data.permission', 'label_attributes' => ['class' => 'govuk-label govuk-checkboxes__label'], 'must_be_value' => 'Y', 'error-message' => 'checkbox.error.operating-centre-permission'])]
    #[Form\Type('\Common\Form\Elements\InputFilters\SingleCheckbox')]
    public $permission;
}
