<?php

/**
 * Group-by-name view of email templates (VOL-7238 deliverable 7).
 *
 * One row per template `name`, with Languages + Formats shown as GOV.UK tag chip-sets.
 * The Action radio carries the row's `primaryVariantId` — clicking Edit opens the modal
 * on the en_GB/md variant (or sensible fallback) and the existing sibling pills handle
 * navigation between variants from there.
 */

return [
    'variables' => [
        'title' => 'Email Templates',
        'titleSingular' => 'Email Templates',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Subject',
            'name' => 'description',
        ],
        [
            'title' => 'Languages',
            'name' => 'locales',
            // Render each locale in the group as a govuk-tag chip. Empty cell when there
            // are no locales (shouldn't happen — every group has at least one variant).
            'formatter' => fn($row) => implode(
                ' ',
                array_map(
                    static fn($locale) => sprintf(
                        '<strong class="govuk-tag govuk-tag--turquoise">%s</strong>',
                        htmlspecialchars(match ($locale) {
                            'en_GB' => 'EN',
                            'cy_GB' => 'CY',
                            'en_CY' => 'EN-CY',
                            default => (string) $locale,
                        }, ENT_QUOTES, 'UTF-8'),
                    ),
                    is_array($row['locales'] ?? null) ? $row['locales'] : [],
                ),
            ),
        ],
        [
            'title' => 'Formats',
            'name' => 'formats',
            // Same chip palette as the per-row chips on the previous list shape (green=md,
            // blue=html, grey=plain) so coverage gaps stand out at a glance.
            'formatter' => fn($row) => implode(
                ' ',
                array_map(
                    static fn($format) => sprintf(
                        '<strong class="govuk-tag govuk-tag--%s">%s</strong>',
                        match ($format) {
                            'md' => 'green',
                            'html' => 'blue',
                            'plain' => 'grey',
                            default => 'grey',
                        },
                        htmlspecialchars(match ($format) {
                            'md' => 'Markdown',
                            'html' => 'HTML',
                            'plain' => 'Plain',
                            default => (string) $format,
                        }, ENT_QUOTES, 'UTF-8'),
                    ),
                    is_array($row['formats'] ?? null) ? $row['formats'] : [],
                ),
            ),
        ],
        [
            'title' => 'markup-table-th-action',
            'width' => 'checkbox',
            // Row's `id` is the primary variant id (en_GB/md > en_GB/html > lowest id) so
            // clicking Edit opens the modal on the group's most useful default variant. The
            // existing radio.phtml partial reads {{id}}.
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
