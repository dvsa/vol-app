<?php

/**
 * The checks the analyser passed on the change document review page, with bank and bank address
 * for reference.
 *
 * Rows come from Olcs\Data\Mapper\FinancialEvidenceAssessmentReview as plain text, so every
 * value is escaped here.
 */

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Pass issues',
    ],
    'attributes' => [
        'name' => 'financialEvidenceAssessmentPasses',
    ],
    'settings' => [
        'hide_title' => true,
    ],
    'columns' => [
        [
            'title' => 'Information',
            'formatter' => static fn(array $row): string => '<strong>' . Escape::html($row['label']) . '</strong>'
                . ($row['value'] !== null ? ': ' . Escape::html($row['value']) : ''),
        ],
        [
            'title' => 'Flag',
            'formatter' => static fn(array $row): string => $row['flag'] === null
                ? ''
                : '<strong class="govuk-tag ' . Escape::htmlAttr((string)$row['flagTag']) . '">'
                    . Escape::html($row['flag']) . '</strong>',
        ],
        [
            'title' => 'Comment',
            // A dash, as on the issues above, so an empty cell reads as "no comment" not missing data.
            'formatter' => static fn(array $row): string => Escape::html((string)($row['comment'] ?? '-')),
        ],
    ],
];
