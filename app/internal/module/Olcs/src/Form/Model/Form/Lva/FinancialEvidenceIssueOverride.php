<?php

namespace Olcs\Form\Model\Form\Lva;

use Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag;
use Laminas\Form\Annotation;

/**
 * Caseworker change of one failed or skipped check to a pass, on the change document review
 * page. The reverse of FinancialEvidencePassOverride; the rules both share, and their wording,
 * are in FinancialEvidenceOverrideRules (a reason is required to change a fail, not a skipped check).
 *
 * One instance serves every issue: the view sets row per issue before rendering it.
 *
 * @codeCoverageIgnore
 */
#[Annotation\Name('financial-evidence-issue-override')]
#[Annotation\Attributes(['method' => 'post'])]
#[Annotation\Type('Common\Form\Form')]
#[Annotation\Options(['prefer_form_input_filter' => true])]
class FinancialEvidenceIssueOverride
{
    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\Digits')]
    public $analysisId = null;

    #[Annotation\Type('Hidden')]
    #[Annotation\Required(true)]
    #[Annotation\Validator('Laminas\Validator\InArray', options: [
        'haystack' => OverrideDocumentAnalysisFlag::ROWS,
        'strict' => true,
    ])]
    public $row = null;

    // Laid out as FinancialEvidencePassOverride's: Yes and No side by side, the label as the
    // radio group's legend (the view must not print the question again), and no default.
    #[Annotation\Required(true)]
    #[Annotation\ErrorMessage(FinancialEvidenceOverrideRules::MSG_ANSWER_REQUIRED)]
    #[Annotation\Type('Radio')]
    #[Annotation\Attributes([
        'radios_wrapper_attributes' => ['class' => 'govuk-radios--standard govuk-radios--inline'],
    ])]
    #[Annotation\Options([
        'label' => 'lva-financial-evidence-assessment-issue-change-to-pass',
        'value_options' => FinancialEvidenceOverrideRules::ANSWER_OPTIONS,
        'fieldset-attributes' => ['class' => 'govuk-fieldset'],
        'legend-attributes' => ['class' => 'govuk-fieldset__legend govuk-fieldset__legend--s'],
    ])]
    public $changeRecommendation = null;

    // See FinancialEvidencePassOverride::$comment for why it is Required with a "null only" NotEmpty.
    #[Annotation\Required(true)]
    #[Annotation\Type('TextArea')]
    #[Annotation\Options(['label' => 'lva-financial-evidence-assessment-issue-reason'])]
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
        'value' => 'override',
    ])]
    #[Annotation\Options(['label' => 'Save and continue'])]
    #[Annotation\Type('\Common\Form\Elements\InputFilters\ActionButton')]
    public $saveOverride = null;
}
