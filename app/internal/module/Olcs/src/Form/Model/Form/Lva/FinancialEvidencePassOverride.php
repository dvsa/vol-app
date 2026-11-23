<?php

namespace Olcs\Form\Model\Form\Lva;

use Dvsa\Olcs\Transfer\Command\Document\OverridePassedDocumentAnalysisFlag;
use Laminas\Form\Annotation;

/**
 * Caseworker change of one passed check to a fail, on the change document review page. The
 * reverse of FinancialEvidenceIssueOverride; the rules both share, and their wording, are in
 * FinancialEvidenceOverrideRules.
 *
 * One instance serves every pass: the view sets row per pass before rendering it.
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

    // Yes and No sit side by side, as the VOL-7338 design draws them. The label is the radio
    // group's legend, shown in bold as the question; the view must not print the question again.
    // No default: neither is selected until the caseworker chooses, as the design shows.
    #[Annotation\Required(true)]
    #[Annotation\ErrorMessage(FinancialEvidenceOverrideRules::MSG_ANSWER_REQUIRED)]
    #[Annotation\Type('Radio')]
    #[Annotation\Attributes([
        'radios_wrapper_attributes' => ['class' => 'govuk-radios--standard govuk-radios--inline'],
    ])]
    #[Annotation\Options([
        'label' => 'lva-financial-evidence-assessment-pass-change-outcome',
        'value_options' => FinancialEvidenceOverrideRules::ANSWER_OPTIONS,
        'fieldset-attributes' => ['class' => 'govuk-fieldset'],
        'legend-attributes' => ['class' => 'govuk-fieldset__legend govuk-fieldset__legend--s'],
    ])]
    public $changeRecommendation = null;

    // Required(true) with the "null only" NotEmpty is what lets an empty box reach the rules
    // below: left alone, a required input fails an empty value with a generic message, and a
    // non-required one skips its validators altogether when empty.
    #[Annotation\Required(true)]
    #[Annotation\Type('TextArea')]
    #[Annotation\Options(['label' => 'lva-financial-evidence-assessment-pass-more-detail'])]
    #[Annotation\Attributes(['class' => 'extra-long', 'required' => false])]
    #[Annotation\Filter('Laminas\Filter\StringTrim')]
    #[Annotation\Validator('Laminas\Validator\NotEmpty', options: ['null'])]
    #[Annotation\Validator('ValidateIf', options: FinancialEvidenceOverrideRules::REASON_REQUIRED)]
    #[Annotation\Validator('Laminas\Validator\StringLength', options: FinancialEvidenceOverrideRules::COMMENT_LENGTH)]
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
