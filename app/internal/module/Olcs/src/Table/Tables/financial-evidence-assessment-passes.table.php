<?php

/**
 * The checks the analyser passed on the change document review page, with bank and bank address
 * for reference.
 *
 * Rows come from Olcs\Data\Mapper\FinancialEvidenceAssessmentReview as plain text, so every
 * value is escaped here.
 */

use Common\Util\Escape;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;

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
            // As everywhere a check is shown: the analyser's flag, greyed out when a caseworker
            // changed it (flagTag is then the superseded grey). The new flag is with their comment.
            'formatter' => static function (array $row): string {
                if ($row['flag'] === null) {
                    return '';
                }

                // This table has no remarks column, so the analyser's remark is offered as the
                // browser's own tooltip on its flag, labelled as IDP's.
                $remark = (string)($row['remark'] ?? '');

                return '<strong class="govuk-tag ' . Escape::htmlAttr((string)$row['flagTag']) . '"'
                    . ($remark !== ''
                        ? ' title="' . Escape::htmlAttr(FinancialEvidenceAssessmentTab::IDP_REMARK_PREFIX . $remark) . '"'
                        : '') . '>'
                    . Escape::html((string)$row['flag']) . '</strong>';
            },
        ],
        [
            'title' => 'Comment',
            // The flag the caseworker changed the check to (when they changed it), the comment,
            // then who wrote it and when on its own line with a little space above. A dash when nobody has answered the check, so an
            // empty cell reads as "no comment" and not as missing data.
            'formatter' => static function (array $row): string {
                $changed = $row['changed'] ?? false;

                if (!$changed && !($row['kept'] ?? false)) {
                    return '-';
                }

                $comment = (string)($row['comment'] ?? '');

                return ($changed
                        ? '<strong class="govuk-tag ' . Escape::htmlAttr((string)$row['changedToTag']) . '">'
                            . Escape::html((string)$row['changedTo']) . '</strong> '
                        : '')
                    . Escape::html($comment !== '' ? $comment : '-')
                    . '<span class="govuk-!-display-block govuk-!-margin-top-2">'
                    . '<strong>' . Escape::html($row['changedBy'] ?? 'Unknown user') . '</strong>'
                    . (($row['changedOn'] ?? null) !== null ? ' ' . Escape::html($row['changedOn']) : '')
                    . '</span>';
            },
        ],
    ],
];
