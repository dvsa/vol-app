<?php

namespace Olcs\Form\Model\Form\Lva;

use Dvsa\Olcs\Transfer\Command\Document\OverridePassedDocumentAnalysisFlag;
use Laminas\Form\Annotation;

/**
 * Caseworker change of one passed check to a fail, on the change document review page. The
 * reverse of FinancialEvidenceIssueOverride.
 *
 * One instance serves every pass: the view sets row per pass before rendering it.
 * Every row this form targets is a genuine pass, so a reason is always required when changing it
 * to a fail; that is enforced by the controller, which also allows an optional note when not.
 *
 * @codeCoverageIgnore
 */
#[Annotation\Name('financial-evidence-pass-override')]
#[Annotation\Attributes(['method' => 'post'])]
#[Annotation\Type('Common\Form\Form')]
#[Annotation\Options(['prefer_form_input_filter' => true])]
class FinancialEvidencePassOverride
{
    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\Digits')]
    public $analysisId = null;

    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\InArray', options: [
        'haystack' => OverridePassedDocumentAnalysisFlag::ROWS,
        'strict' => true,
    ])]
    public $row = null;

    #[Annotation\Required(true)]
    #[Annotation\Type('Radio')]
    #[Annotation\Attributes(['radios_wrapper_attributes' => ['class' => 'govuk-radios--standard'], 'value' => 'Y'])]
    #[Annotation\Options([
        'label' => 'Do you want to change the outcome of this issue?',
        'value_options' => ['Y' => 'Yes', 'N' => 'No'],
    ])]
    public $changeRecommendation = null;

    #[Annotation\Type('TextArea')]
    #[Annotation\Options(['label' => 'Can you provide more detail?'])]
    #[Annotation\Attributes(['class' => 'extra-long'])]
    #[Annotation\Filter('Laminas\Filter\StringTrim')]
    #[Annotation\Validator('Laminas\Validator\StringLength', options: ['max' => 1000])]
    public $comment = null;

    #[Annotation\Attributes([
        'data-module' => 'govuk-button',
        'type' => 'submit',
        'class' => 'govuk-button govuk-button--secondary',
        'value' => 'override-fail',
    ])]
    #[Annotation\Options(['label' => 'Save and continue'])]
    #[Annotation\Type('\Common\Form\Elements\InputFilters\ActionButton')]
    public $savePassOverride = null;
}
