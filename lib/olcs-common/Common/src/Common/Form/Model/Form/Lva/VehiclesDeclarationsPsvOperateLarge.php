<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

#[Form\Options(['prefer_form_input_filter' => true])]
#[Form\Name('lva-vehicles-declarations-large')]
#[Form\Attributes(['method' => 'post'])]
#[Form\Type('Common\Form\Form')]
class VehiclesDeclarationsPsvOperateLarge
{
    #[Form\Name('version')]
    #[Form\Type('Hidden')]
    public $version;

    #[Form\Options(['label' => 'application_vehicle-safety_undertakings.nineOrMore.label'])]
    #[Form\Type('\Common\Form\Elements\Types\Html')]
    public $psvNoSmallVhlConfirmationLabel;

    #[Form\Attributes(['id' => '', 'placeholder' => ''])]
    #[Form\Options(['label' => 'application_vehicle-safety_undertakings.nineOrMore.details', 'label_attributes' => ['class' => 'govuk-label govuk-checkboxes__label'], 'checked_value' => 'Y', 'unchecked_value' => 'N', 'must_be_value' => 'Y', 'use_hidden_element' => false, 'error-message' => 'checkbox.error.psv-large-vehicles'])]
    #[Form\Type('\Common\Form\Elements\InputFilters\SingleCheckbox')]
    public $psvNoSmallVhlConfirmation;

    #[Form\Name('form-actions')]
    #[Form\ComposedObject('Common\Form\Model\Form\Lva\Fieldset\FormActions')]
    #[Form\Attributes(['class' => 'govuk-button-group'])]
    public $formActions;
}
