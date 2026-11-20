<?php

namespace Olcs\Form\Model\Form\Lva;

use Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag;
use Laminas\Form\Annotation;

/**
 * Caseworker change of one failed or skipped check to a pass, on the change document review page.
 *
 * One instance serves every issue: the view sets row per issue before rendering it.
 * The comment is required only when the caseworker chooses to change the recommendation.
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

    public const string MSG_SELECT_YES_OR_NO = 'Select yes if you want to change this recommendation to a pass';
    public const string MSG_ENTER_REASON = 'Enter a reason for changing the recommendation';
    public const string MSG_REASON_TOO_LONG = 'Reason for the change must be 1,000 characters or fewer';

    // Yes and No sit side by side, as the design draws them. The label is the radio group's
    // legend, shown in bold as the question; the view must not print the question again.
    // No default: neither is selected until the caseworker chooses.
    #[Annotation\Required(true)]
    #[Annotation\Type('Radio')]
    #[Annotation\Attributes([
        'radios_wrapper_attributes' => ['class' => 'govuk-radios--standard govuk-radios--inline'],
    ])]
    #[Annotation\Options([
        'label' => 'Change this recommendation to a pass?',
        'value_options' => ['N' => 'No', 'Y' => 'Yes'],
        'fieldset-attributes' => ['class' => 'govuk-fieldset'],
        'legend-attributes' => ['class' => 'govuk-fieldset__legend govuk-fieldset__legend--s'],
    ])]
    #[Annotation\Validator('Laminas\Validator\NotEmpty', options: [
        'messages' => ['isEmpty' => self::MSG_SELECT_YES_OR_NO],
    ])]
    public $changeRecommendation = null;

    // The plain NotEmpty (null only) stops Laminas adding its own "required" check, so the reason
    // may be blank for "No"; ValidateIf requires it, up to 1,000 characters, only for "Yes".
    #[Annotation\Required(true)]
    #[Annotation\Type('TextArea')]
    #[Annotation\Options(['label' => 'Reason for the change'])]
    #[Annotation\Attributes(['class' => 'extra-long', 'required' => false])]
    #[Annotation\Filter('Laminas\Filter\StringTrim')]
    #[Annotation\Validator('Laminas\Validator\NotEmpty', options: ['null'])]
    #[Annotation\Validator('ValidateIf', options: [
        'context_field' => 'changeRecommendation',
        'context_values' => ['Y'],
        'allow_empty' => false,
        'validators' => [
            [
                'name' => 'Laminas\Validator\NotEmpty',
                'options' => ['messages' => ['isEmpty' => self::MSG_ENTER_REASON]],
                'break_chain_on_failure' => true,
            ],
            [
                'name' => 'Laminas\Validator\StringLength',
                'options' => ['max' => 1000, 'messages' => ['stringLengthTooLong' => self::MSG_REASON_TOO_LONG]],
            ],
        ],
    ])]
    public $comment = null;

    #[Annotation\Attributes([
        'data-module' => 'govuk-button',
        'type' => 'submit',
        'class' => 'govuk-button',
        'value' => 'override',
    ])]
    #[Annotation\Options(['label' => 'Save and continue'])]
    #[Annotation\Type('\Common\Form\Elements\InputFilters\ActionButton')]
    public $saveOverride = null;
}
