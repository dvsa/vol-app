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
    // Worded as GOV.UK error messages are: they say how to put it right and name the thing
    // ("Select yes if...", "... must be [number] characters or fewer"). The missing-reason message
    // is the exception: its wording is set by VOL-7338 AC2.
    public const string MSG_ANSWER_REQUIRED = 'Select yes if you want to change the outcome of this issue';
    public const string MSG_REASON_REQUIRED = 'You must provide explanatory text.';
    public const string MSG_COMMENT_TOO_LONG = 'More detail must be 1,000 characters or fewer';

    /** The same limit the API's command applies. */
    public const int COMMENT_MAX_LENGTH = 1000;

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
    // Neither is selected until the caseworker chooses, so a save can arrive with no answer.
    #[Annotation\ErrorMessage(self::MSG_ANSWER_REQUIRED)]
    #[Annotation\Type('Radio')]
    // Yes and No sit side by side, as the VOL-7338 design draws them. The label is the radio
    // group's legend, shown in bold as the question; the view must not print the question again.
    // No default: neither is selected until the caseworker chooses, as the design shows.
    #[Annotation\Attributes([
        'radios_wrapper_attributes' => ['class' => 'govuk-radios--standard govuk-radios--inline'],
    ])]
    #[Annotation\Options([
        'label' => 'Do you want to change the outcome of this issue?',
        'value_options' => ['Y' => 'Yes', 'N' => 'No'],
        'fieldset-attributes' => ['class' => 'govuk-fieldset'],
        'legend-attributes' => ['class' => 'govuk-fieldset__legend govuk-fieldset__legend--s'],
    ])]
    public $changeRecommendation = null;

    // All of the text box's rules live here, with their wording, so the form catches them before
    // anything is sent and the controller only has to show the form's messages:
    //
    // - "Yes" needs a reason (every check on this form is a genuine pass, so changing it to a fail
    //   always does). "No" means the analyser's outcome stands and needs none.
    // - At most 1,000 characters, whichever answer is given.
    //
    // Required(true) with the "null only" NotEmpty is what lets an empty box reach those rules:
    // left alone, a required input fails an empty value with a generic message, and a
    // non-required one skips its validators altogether when empty.
    #[Annotation\Required(true)]
    #[Annotation\Type('TextArea')]
    #[Annotation\Options(['label' => 'Can you provide more detail?'])]
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
                'options' => ['messages' => ['isEmpty' => self::MSG_REASON_REQUIRED]],
            ],
        ],
    ])]
    #[Annotation\Validator('Laminas\Validator\StringLength', options: [
        'max' => self::COMMENT_MAX_LENGTH,
        'messages' => ['stringLengthTooLong' => self::MSG_COMMENT_TOO_LONG],
    ])]
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
