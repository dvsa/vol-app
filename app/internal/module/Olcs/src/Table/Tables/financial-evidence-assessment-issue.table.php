<?php

/**
 * One issue on the change document review page: the infringing value (when there is one), the
 * flag, the remark and, once a caseworker has changed it, their comment.
 *
 * Rows come from Olcs\Data\Mapper\FinancialEvidenceAssessmentReview as plain text, so every
 * value is escaped here; remarks are model-generated and comments are free text.
 */

use Common\Util\Escape;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentReview as Mapper;

return [
    'variables' => [
        'title' => 'Issue',
    ],
    'attributes' => [
        'name' => 'financialEvidenceAssessmentIssue',
    ],
    'settings' => [
        'hide_title' => true,
    ],
    'columns' => [
        [
            'title' => 'Check',
            'formatter' => static fn(array $row): string => '<strong>' . Escape::html($row['heading']) . '</strong>',
        ],
        [
            'title' => 'Result',
            'formatter' => static fn(array $row): string => match ($row['type']) {
                Mapper::ROW_FLAG => '<strong class="govuk-tag ' . Escape::htmlAttr((string)$row['flagTag']) . '">'
                    . Escape::html((string)$row['flag']) . '</strong>',
                Mapper::ROW_COMMENT => '<strong>' . Escape::html((string)($row['changedBy'] ?? 'Unknown user'))
                    . '</strong>: ' . Escape::html((string)($row['text'] ?? '')),
                default => Escape::html((string)($row['text'] ?? '')),
            },
        ],
        [
            'title' => 'Change',
            'formatter' => static fn(array $row): string => $row['type'] !== Mapper::ROW_FLAG || $row['changedTo'] === null
                ? ''
                : '<strong class="govuk-tag ' . Escape::htmlAttr((string)$row['changedToTag']) . '">'
                    . Escape::html((string)$row['changedTo']) . '</strong>'
                    . ' changed by <strong>' . Escape::html((string)($row['changedBy'] ?? 'Unknown user')) . '</strong>',
        ],
    ],
];



