<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Lva;

use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;

/**
 * Rules shared by FinancialEvidenceIssueOverride and FinancialEvidencePassOverride: an answer is
 * required; "Yes" needs a reason unless the analyser skipped the check; the comment is at most
 * 1,000 characters either way (the limit the API's commands also apply).
 *
 * The analyser flag is added to the form's data by the controller under CONTEXT_ANALYSER_FLAG,
 * since it is not something the caseworker posts.
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
     * On "Yes", and unless the check was skipped, the comment must not be empty.
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
