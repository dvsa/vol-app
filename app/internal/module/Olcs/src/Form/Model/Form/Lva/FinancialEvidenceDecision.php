<?php

namespace Olcs\Form\Model\Form\Lva;

use Laminas\Form\Annotation;

/**
 * Caseworker decision on the financial evidence, on the change document review page.
 * Whether the evidence can be accepted is decided by the API, not here.
 *
 * @codeCoverageIgnore
 */
#[Annotation\Name('financial-evidence-decision')]
#[Annotation\Attributes(['method' => 'post'])]
#[Annotation\Type('Common\Form\Form')]
#[Annotation\Options(['prefer_form_input_filter' => true])]
class FinancialEvidenceDecision
{
    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\Digits')]
    public $analysisId = null;

    #[Annotation\Required(true)]
    #[Annotation\Type('Radio')]
    #[Annotation\Attributes(['radios_wrapper_attributes' => ['class' => 'govuk-radios--standard']])]
    #[Annotation\Options([
        'label' => 'Select your decision',
        'fieldset-attributes' => ['class' => 'govuk-fieldset'],
        'legend-attributes' => ['class' => 'govuk-fieldset__legend govuk-fieldset__legend--l'],
        'value_options' => [
            'APPROVED' => 'Accept financial evidence',
            'REJECTED' => 'Reject financial evidence',
        ],
    ])]
    public $decision = null;

    #[Annotation\Attributes([
        'data-module' => 'govuk-button',
        'type' => 'submit',
        'class' => 'govuk-button',
        'value' => 'decide',
    ])]
    #[Annotation\Options(['label' => 'Save'])]
    #[Annotation\Type('\Common\Form\Elements\InputFilters\ActionButton')]
    public $saveDecision = null;
}
