<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('convictionsConfirmation')]
class ConvictionsPenaltiesConfirmation
{
    #[Form\Attributes(['id' => ''])]
    #[Form\Options(['checked_value' => 'Y', 'unchecked_value' => 'N', 'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-labelConfirm', 'label_attributes' => ['class' => 'govuk-label govuk-checkboxes__label'], 'must_be_value' => 'Y', 'error-message' => 'checkbox.error.convictions-confirmation'])]
    #[Form\Type('\Common\Form\Elements\InputFilters\SingleCheckbox')]
    public $convictionsConfirmation;
}
