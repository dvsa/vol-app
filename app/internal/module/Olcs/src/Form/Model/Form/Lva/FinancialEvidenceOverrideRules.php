<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Lva;

use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;

/**
 * The rules shared by the two forms a caseworker answers a check with on the change document
 * review page: FinancialEvidenceIssueOverride (a failed or skipped check to a pass) and
 * FinancialEvidencePassOverride (a passed check to a fail). Both ask the same question of the
 * same fields, so the rules, their limits and their wording live here once and both forms
 * refer to them from their attributes.
 *
 * - An answer is required: neither Yes nor No is selected until the caseworker chooses.
 * - "Yes" needs a reason, unless the analyser skipped the check (it was never assessed, so there
 *   is no outcome to overturn). "No" means the analyser's outcome stands and needs none.
 * - The text is at most 1,000 characters, whichever answer is given - the same limit the API's
 *   commands apply.
 *
 * Whether the check was skipped is not something the caseworker posts: the controller adds the
 * check's analyser flag to the form's data, under CONTEXT_ANALYSER_FLAG, from the page it built.
 *
 * Messages are translation keys; the text is in the translations database.
 */
final class FinancialEvidenceOverrideRules
{
    public const string MSG_ANSWER_REQUIRED = 'lva-financial-evidence-assessment-error-answer-required';
    public const string MSG_REASON_REQUIRED = 'lva-financial-evidence-assessment-error-reason-required';
    public const string MSG_COMMENT_TOO_LONG = 'lva-financial-evidence-assessment-error-comment-too-long';

    /** The same limit the API's commands apply. */
    public const int COMMENT_MAX_LENGTH = 1000;

    /** The data key, set by the controller, holding the analyser's own flag for the check. */
    public const string CONTEXT_ANALYSER_FLAG = 'analyserFlag';

    /** The fields the caseworker fills in; the rest are hidden, so an error there is not theirs to fix. */
    public const array CASEWORKER_FIELDS = ['changeRecommendation', 'comment'];

    public const array ANSWER_OPTIONS = ['Y' => 'Yes', 'N' => 'No'];

    /** Options for the comment's StringLength validator. */
    public const array COMMENT_LENGTH = [
        'max' => self::COMMENT_MAX_LENGTH,
        'messages' => ['stringLengthTooLong' => self::MSG_COMMENT_TOO_LONG],
    ];

    /**
     * Options for the comment's ValidateIf validator: on "Yes", and unless the check was skipped,
     * the comment must not be empty.
     */
    public const array REASON_REQUIRED = [
        'context_field' => 'changeRecommendation',
        'context_values' => ['Y'],
        'allow_empty' => false,
        'validators' => [
            [
                'name' => 'ValidateIf',
                'options' => [
                    'context_field' => self::CONTEXT_ANALYSER_FLAG,
                    'context_values' => [FinancialEvidenceAssessmentTab::FLAG_SKIPPED],
                    'context_truth' => false,
                    'allow_empty' => false,
                    'validators' => [
                        [
                            'name' => 'Laminas\Validator\NotEmpty',
                            'options' => ['messages' => ['isEmpty' => self::MSG_REASON_REQUIRED]],
                        ],
                    ],
                ],
            ],
        ],
    ];
}
