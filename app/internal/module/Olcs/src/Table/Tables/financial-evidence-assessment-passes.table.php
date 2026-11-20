<?php

/**
 * The checks the analyser passed on the change document review page, with bank and bank address
 * for reference.
 *
 * Rows come from Olcs\Data\Mapper\FinancialEvidenceAssessmentReview as plain text, so every
 * value is escaped here. Titles are translation keys, which the table builder translates. Text
 * inside a cell is translated with the table builder's own translator ($this->translator), as
 * other table definitions do, which is why the formatters are not static. The table builder
 * includes this file from inside one of its methods, so $this is the builder; each formatter
 * says so in a docblock, which is what static analysis needs to accept it.
 */

use Common\Service\Table\TableBuilder;
use Common\Util\Escape;
use Olcs\Data\Mapper\FinancialEvidenceAssessmentTab;

return [
    'variables' => [
        'title' => 'lva-financial-evidence-assessment-pass-issues',
    ],
    'attributes' => [
        'name' => 'financialEvidenceAssessmentPasses',
    ],
    'settings' => [
        'hide_title' => true,
    ],
    'columns' => [
        [
            'title' => 'lva-financial-evidence-assessment-column-information',
            // The check's name is a translation key.
            'formatter' => function (array $row): string {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return '<strong>' . Escape::html($this->translator->translate($row['label'])) . '</strong>'
                    . ($row['value'] !== null ? ': ' . Escape::html($row['value']) : '');
            },
        ],
        [
            'title' => 'lva-financial-evidence-assessment-column-flag',
            // As everywhere a check is shown: the analyser's flag, greyed out when a caseworker
            // changed it (flagTag is then the superseded grey). The new flag is with their comment.
            'formatter' => function (array $row): string {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                if ($row['flag'] === null) {
                    return '';
                }

                // Every flag carries the analyser's remark as the browser's own tooltip, labelled as
                // IDP's, wherever a check is shown. This table has no remarks column, so here it
                // is the only place the remark appears.
                $remark = (string)($row['remark'] ?? '');
                $tooltip = $this->translator->translate(FinancialEvidenceAssessmentTab::IDP_REMARK_PREFIX) . $remark;

                return '<strong class="govuk-tag ' . Escape::htmlAttr((string)$row['flagTag']) . '"'
                    . ($remark !== '' ? ' title="' . Escape::htmlAttr($tooltip) . '"' : '') . '>'
                    . Escape::html($this->translator->translate(
                        FinancialEvidenceAssessmentTab::flagTranslationKey((string)$row['flag'])
                    ))
                    . '</strong>';
            },
        ],
        [
            'title' => 'lva-financial-evidence-assessment-comment',
            // The flag the caseworker changed the check to (when they changed it), the comment,
            // then who wrote it and when on its own line with a little space above. A dash when
            // nobody has answered the check, so an empty cell reads as "no comment" and not as
            // missing data.
            'formatter' => function (array $row): string {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $changed = $row['changed'] ?? false;

                if (!$changed && !($row['kept'] ?? false)) {
                    return '-';
                }

                $comment = (string)($row['comment'] ?? '');
                $author = $row['changedBy']
                    ?? $this->translator->translate('lva-financial-evidence-assessment-unknown-user');

                return ($changed
                        ? '<strong class="govuk-tag ' . Escape::htmlAttr((string)$row['changedToTag']) . '">'
                            . Escape::html($this->translator->translate(
                                FinancialEvidenceAssessmentTab::flagTranslationKey((string)$row['changedTo'])
                            ))
                            . '</strong> '
                        : '')
                    . Escape::html($comment !== '' ? $comment : '-')
                    . '<span class="govuk-!-display-block govuk-!-margin-top-2">'
                    . '<strong>' . Escape::html($author) . '</strong>'
                    . (($row['changedOn'] ?? null) !== null ? ' ' . Escape::html($row['changedOn']) : '')
                    . '</span>';
            },
        ],
    ],
];
